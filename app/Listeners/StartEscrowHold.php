<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Models\Setting;
use App\Models\SellerEarning;

class StartEscrowHold
{
    public function handle(OrderCompleted $event): void
    {
        $vendorHoldDays = Setting::get('vendor', 'hold_period_days', null);
        $escrowDays = (int) ($vendorHoldDays ?? Setting::get('refund_escrow', 'escrow_period_days', 14));

        SellerEarning::where('order_id', $event->order->id)
            ->where('status', 'pending')
            ->update([
                'status'            => 'held',
                'escrow_expires_at' => now()->addDays($escrowDays),
            ]);
    }
}
