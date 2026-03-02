<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Services\SellerBalanceService;

class ActivateSellerEarnings
{
    public function handle(OrderPaid $event): void
    {
        app(SellerBalanceService::class)->onPaymentConfirmed($event->order->id);
    }
}
