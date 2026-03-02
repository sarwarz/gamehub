<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Services\OrderNotificationService;

class SendCompletionNotifications
{
    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;
        $order->load(['user', 'items.product', 'items.seller', 'items.deliveries', 'invoice']);

        OrderNotificationService::orderCompleted($order);
    }
}
