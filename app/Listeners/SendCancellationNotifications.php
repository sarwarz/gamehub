<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Services\OrderNotificationService;

class SendCancellationNotifications
{
    public function handle(OrderCancelled $event): void
    {
        OrderNotificationService::orderCancelled($event->order);
    }
}
