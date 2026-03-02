<?php

namespace App\Listeners;

use App\Events\OrderRefunded;
use App\Services\OrderNotificationService;

class SendRefundNotifications
{
    public function handle(OrderRefunded $event): void
    {
        $order = $event->order;
        $order->loadMissing(['user', 'items.product', 'items.seller']);

        OrderNotificationService::orderRefunded($order);
    }
}
