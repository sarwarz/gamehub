<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OrderDeliveryService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AutoDeliverOrderJob implements ShouldQueue
{
    public function __construct(public int $orderId) {}

    public function handle()
    {
        $order = Order::with('items.deliveries')->findOrFail($this->orderId);

        foreach ($order->items as $item) {
            foreach ($item->deliveries as $delivery) {
                if ($delivery->delivery_method === 'auto') {
                    app(OrderDeliveryService::class)
                        ->autoDeliver($delivery);
                }
            }
        }
    }
}
