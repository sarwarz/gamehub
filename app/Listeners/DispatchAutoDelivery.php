<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Jobs\AutoDeliverOrderJob;

class DispatchAutoDelivery
{
    public function handle(OrderPaid $event): void
    {
        dispatch(new AutoDeliverOrderJob($event->order->id));
    }
}
