<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\Coupon;

class IncrementCouponUsage
{
    /**
     * Coupon usage is now atomically incremented at checkout session creation
     * (with lockForUpdate) to prevent TOCTOU race conditions. This listener
     * is kept as a no-op for backward compatibility with the event system.
     */
    public function handle(OrderPaid $event): void
    {
        // No-op: coupon `used` is now incremented at checkout time with a lock.
    }
}
