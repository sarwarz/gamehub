<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Models\SellerEarning;

class RevertSellerEarnings
{
    public function handle(OrderCancelled $event): void
    {
        try {
            SellerEarning::where('order_id', $event->order->id)
                ->whereIn('status', ['pending', 'held'])
                ->delete();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('RevertSellerEarnings failed for order #' . $event->order->id . ': ' . $e->getMessage());
            report($e);
        }
    }
}
