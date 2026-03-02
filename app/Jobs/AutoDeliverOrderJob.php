<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderNote;
use App\Events\OrderCompleted;
use App\Services\OrderDeliveryService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;

class AutoDeliverOrderJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [30, 120, 600];
    public int $maxExceptions = 2;

    public function __construct(public int $orderId) {}

    public function uniqueId(): string
    {
        return 'auto-deliver-' . $this->orderId;
    }

    public function handle(): void
    {
        $order = Order::with('items.deliveries')->findOrFail($this->orderId);

        // Keys were pre-assigned at checkout — fire completion events
        if ($order->status === 'completed') {
            event(new OrderCompleted($order));
            return;
        }

        $allDelivered = true;

        foreach ($order->items as $item) {
            foreach ($item->deliveries as $delivery) {
                if ($delivery->delivery_method === 'auto' && $delivery->status === 'pending') {
                    app(OrderDeliveryService::class)->autoDeliver($delivery);

                    if ($delivery->fresh()->status !== 'delivered') {
                        $allDelivered = false;
                    }
                } elseif ($delivery->status !== 'delivered') {
                    $allDelivered = false;
                }
            }
        }

        $order->refresh();

        if ($allDelivered && $order->status !== 'completed') {
            $order->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
            event(new OrderCompleted($order->fresh()));
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('AutoDeliverOrderJob permanently failed', [
            'order_id' => $this->orderId,
            'error'    => $exception->getMessage(),
        ]);

        try {
            OrderNote::create([
                'order_id' => $this->orderId,
                'note'     => 'Auto-delivery failed after all retries: ' . $exception->getMessage(),
                'type'     => 'system',
                'is_visible_to_customer' => false,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to create order note', ['error' => $e->getMessage()]);
        }

        try {
            if (\App\Models\Setting::get('order_notifications', 'admin_on_delivery_failed', true)) {
                $admins = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'superadmin']))->get();
                $order = \App\Models\Order::find($this->orderId);
                if ($order) {
                    $admins->each(fn($a) => $a->notify(new \App\Notifications\DeliveryFailedNotification($order, 'admin', $exception->getMessage())));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Delivery failed notification error: ' . $e->getMessage());
        }
    }
}
