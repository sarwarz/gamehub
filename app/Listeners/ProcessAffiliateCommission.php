<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Services\AffiliateService;

class ProcessAffiliateCommission
{
    public function handle(OrderPaid $event): void
    {
        AffiliateService::processOrderCommission($event->order);
    }
}
