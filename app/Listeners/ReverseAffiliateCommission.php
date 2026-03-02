<?php

namespace App\Listeners;

use App\Services\AffiliateService;

class ReverseAffiliateCommission
{
    public function handle(object $event): void
    {
        $reason = match (true) {
            $event instanceof \App\Events\OrderCancelled => 'Order cancelled',
            $event instanceof \App\Events\OrderRefunded  => 'Order refunded',
            default => 'Order reversed',
        };

        AffiliateService::reverseCommissions($event->order->id, $reason);
    }
}
