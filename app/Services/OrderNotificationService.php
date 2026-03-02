<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\OrderPaymentNotification;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusNotification;
use Illuminate\Support\Facades\Log;

class OrderNotificationService
{
    protected static function enabled(string $key): bool
    {
        return (bool) Setting::get('order_notifications', $key, false);
    }

    protected static function getAdmins()
    {
        return User::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'superadmin']))->get();
    }

    protected static function getSellers(Order $order)
    {
        $order->loadMissing('items.seller.user');
        return $order->items
            ->pluck('seller.user')
            ->filter()
            ->unique('id')
            ->values();
    }

    /**
     * Order placed — notify customer, sellers, admins
     */
    public static function orderPlaced(Order $order): void
    {
        $order->loadMissing(['user', 'items.seller']);

        try {
            if (self::enabled('customer_on_placed') && $order->user) {
                $order->user->notify(new OrderPlacedNotification($order, 'customer'));
            }
        } catch (\Throwable $e) {
            Log::warning('Order placed customer notification failed: ' . $e->getMessage());
        }

        try {
            if (self::enabled('seller_on_placed')) {
                self::getSellers($order)->each(fn ($s) =>
                    $s->notify(new OrderPlacedNotification($order, 'seller'))
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Order placed seller notification failed: ' . $e->getMessage());
        }

        try {
            if (self::enabled('admin_on_placed')) {
                self::getAdmins()->each(fn ($a) =>
                    $a->notify(new OrderPlacedNotification($order, 'admin'))
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Order placed admin notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Payment confirmed — notify customer (with invoice), admins (only for non-instant payments)
     */
    public static function paymentConfirmed(Order $order): void
    {
        $order->loadMissing('user');

        try {
            if (self::enabled('customer_on_paid') && $order->user) {
                $order->user->notify(new OrderPaymentNotification($order, 'customer'));
            }
        } catch (\Throwable $e) {
            Log::warning('Payment confirmed customer notification failed: ' . $e->getMessage());
        }

        $isInstantPayment = $order->payment_method === 'wallet';
        if ($isInstantPayment) {
            return;
        }

        try {
            if (self::enabled('admin_on_paid')) {
                self::getAdmins()->each(fn ($a) =>
                    $a->notify(new OrderPaymentNotification($order, 'admin'))
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Payment confirmed admin notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Status changed (processing) — notify customer
     */
    public static function statusChanged(Order $order, string $newStatus): void
    {
        if (!self::enabled('customer_on_status_change')) return;

        $order->loadMissing('user');
        if (!$order->user) return;

        try {
            $order->user->notify(new OrderStatusNotification($order, $newStatus, 'customer'));
        } catch (\Throwable $e) {
            Log::warning("Order status notification failed: " . $e->getMessage());
        }
    }

    /**
     * Order completed — notify customer, sellers
     */
    public static function orderCompleted(Order $order): void
    {
        $order->loadMissing(['user', 'items.seller']);

        try {
            if (self::enabled('customer_on_completed') && $order->user) {
                $order->user->notify(new OrderStatusNotification($order, 'completed', 'customer'));
            }
        } catch (\Throwable $e) {
            Log::warning('Order completed customer notification failed: ' . $e->getMessage());
        }

        try {
            if (self::enabled('seller_on_completed')) {
                self::getSellers($order)->each(fn ($s) =>
                    $s->notify(new OrderStatusNotification($order, 'completed', 'seller'))
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Order completed seller notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Order cancelled — notify customer, sellers, admins
     */
    public static function orderCancelled(Order $order): void
    {
        $order->loadMissing(['user', 'items.seller']);

        try {
            if (self::enabled('customer_on_cancelled') && $order->user) {
                $order->user->notify(new OrderStatusNotification($order, 'cancelled', 'customer'));
            }
        } catch (\Throwable $e) {
            Log::warning('Order cancelled customer notification failed: ' . $e->getMessage());
        }

        try {
            if (self::enabled('seller_on_cancelled')) {
                self::getSellers($order)->each(fn ($s) =>
                    $s->notify(new OrderStatusNotification($order, 'cancelled', 'seller'))
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Order cancelled seller notification failed: ' . $e->getMessage());
        }

        try {
            if (self::enabled('admin_on_cancelled')) {
                self::getAdmins()->each(fn ($a) =>
                    $a->notify(new OrderStatusNotification($order, 'cancelled', 'admin'))
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Order cancelled admin notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Order refunded — notify customer, sellers
     */
    public static function orderRefunded(Order $order): void
    {
        $order->loadMissing(['user', 'items.seller']);

        try {
            if (self::enabled('customer_on_refunded') && $order->user) {
                $order->user->notify(new OrderStatusNotification($order, 'refunded', 'customer'));
            }
        } catch (\Throwable $e) {
            Log::warning('Order refunded customer notification failed: ' . $e->getMessage());
        }

        try {
            if (self::enabled('seller_on_refunded')) {
                self::getSellers($order)->each(fn ($s) =>
                    $s->notify(new OrderStatusNotification($order, 'refunded', 'seller'))
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Order refunded seller notification failed: ' . $e->getMessage());
        }
    }
}
