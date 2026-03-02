<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderNote;
use App\Models\Setting;
use App\Events\OrderCompleted;
use App\Services\SellerBalanceService;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderDeliveryService
{
    /**
     * Auto deliver (entry point)
     */
    public function autoDeliver(OrderDelivery $delivery): void
    {
        DB::transaction(function () use ($delivery) {

            // 🔒 Lock delivery row
            $delivery = OrderDelivery::lockForUpdate()->find($delivery->id);

            if (!$delivery || $delivery->status !== 'pending') {
                return;
            }

            $orderItem = $delivery->orderItem;
            $order     = $orderItem->order;

            // Safety check
            if ($order->payment_status !== 'paid') {
                throw new \Exception('Order not paid');
            }

            try {
                // 🔑 Deliver licenses
                $payload = app(LicenseDeliveryService::class)
                    ->deliver($orderItem);

                // ✅ Mark delivered
                $this->completeDeliveryInternal($delivery, $payload);

            } catch (Throwable $e) {

                $delivery->update([
                    'status'  => 'failed',
                    'payload' => [
                        'error' => $e->getMessage(),
                    ],
                ]);

                DB::afterCommit(function () use ($order, $orderItem, $e) {
                    $order->notes()->create([
                        'note' => "Auto delivery failed for order item ID {$orderItem->id}. Error: {$e->getMessage()}",
                        'type' => 'system',
                        'is_visible_to_customer' => false,
                    ]);

                    try {
                        if (Setting::get('order_notifications', 'admin_on_delivery_failed', true)) {
                            $admins = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'superadmin']))->get();
                            $admins->each(fn($a) => $a->notify(new \App\Notifications\DeliveryFailedNotification($order, 'admin', $e->getMessage())));
                        }
                    } catch (\Throwable $ex) {
                        \Illuminate\Support\Facades\Log::warning('Delivery failed notification error: ' . $ex->getMessage());
                    }
                });

                report($e);
                return;
            }

        });
    }

    /**
     * Internal delivery completion (NO TRANSACTION HERE)
     */
    protected function completeDeliveryInternal(
        OrderDelivery $delivery,
        array $payload = []
    ): void {
        $delivery->update([
            'status'       => 'delivered',
            'payload'      => $payload,
            'delivered_at' => now(),
        ]);

        
        $orderItem = $delivery->orderItem;

        // Check item completion
        $allDelivered = $orderItem->deliveries()
            ->where('status', '!=', 'delivered')
            ->doesntExist();

        if ($allDelivered) {
            $orderItem->update([
                'delivery_status' => 'delivered',
            ]);
        }

        $this->completeOrderIfReady($orderItem->order);
    }

    /**
     * Complete order if all items delivered
     */
    protected function completeOrderIfReady(Order $order): void
    {
        if ($order->status === 'completed') {
            return;
        }

        $hasPendingItems = $order->items()
            ->where('delivery_status', '!=', 'delivered')
            ->exists();

        if ($hasPendingItems) {
            return;
        }

        $order->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        app(SellerBalanceService::class)->onOrderCompleted($order->id);

        $order->notes()->create([
            'note' => 'Order auto-completed after successful delivery of all items. Seller earnings released.',
            'type' => 'system',
            'is_visible_to_customer' => true,
        ]);

        DB::afterCommit(function () use ($order) {
            event(new OrderCompleted($order->fresh()));
        });
    }
}
