<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\Setting;
use App\Services\InvoiceService;

class GenerateInvoice
{
    public function handle(OrderPaid $event): void
    {
        if (!Setting::get('invoice', 'auto_generate', true)) {
            return;
        }

        app(InvoiceService::class)->generateFromOrder($event->order->fresh());
    }
}
