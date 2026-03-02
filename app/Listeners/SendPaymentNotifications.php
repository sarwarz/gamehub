<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Services\OrderNotificationService;

class SendPaymentNotifications
{
    public function handle(OrderPaid $event): void
    {
        OrderNotificationService::paymentConfirmed($event->order);
        OrderNotificationService::statusChanged($event->order, 'processing');
    }
}
