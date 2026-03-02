<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Services\AffiliateService;

class HoldAffiliateCommission
{
    public function handle(OrderCompleted $event): void
    {
        AffiliateService::holdCommissions($event->order->id);
    }
}
