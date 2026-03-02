<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Models\Coupon;

class RestoreCouponUsage
{
    public function handle(OrderCancelled $event): void
    {
        try {
            $couponData = $event->order->meta['coupon'] ?? null;

            if ($couponData && !empty($couponData['coupon_id'])) {
                Coupon::where('id', $couponData['coupon_id'])
                    ->where('used', '>', 0)
                    ->decrement('used');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('RestoreCouponUsage failed for order #' . $event->order->id . ': ' . $e->getMessage());
            report($e);
        }
    }
}
