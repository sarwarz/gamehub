<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * Generate invoice from a PAID order
     *
     * This method is intended to be called ONLY
     * after payment is confirmed (webhook success).
     *
     * - Invoice is ISSUED here
     * - Invoice is also MARKED AS PAID here
     */
    public function generateFromOrder(Order $order): Invoice
    {
        return DB::transaction(function () use ($order) {

            /**
             * Safety check:
             * Order must be paid before invoice generation
             */
            if ($order->payment_status !== 'paid') {
                throw new \RuntimeException('Cannot generate invoice for unpaid order.');
            }

            /**
             * Prevent duplicate invoice generation
             */
            if ($order->invoice) {
                return $order->invoice;
            }

            /**
             * Calculate subtotal from order items
             */
            $subtotal = $order->items->sum('subtotal');

            /**
             * Create invoice
             *
             * Because this is called from PAYMENT WEBHOOK:
             * - Invoice is ISSUED now
             * - Invoice is PAID now
             */
            $invoice = Invoice::create([
                'order_id'       => $order->id,
                'user_id'        => $order->user_id, // nullable for guest
                'invoice_number' => $this->generateInvoiceNumber(),

                // Dates
                'issued_at'      => now(),
                'paid_at'        => now(),

                // Amounts
                'subtotal'       => $subtotal,
                'tax_total'      => 0,
                'discount_total' => 0,
                'grand_total'    => $subtotal,
                'currency'       => $order->currency,

                // Status
                'status'         => 'paid',
            ]);

            /**
             * Create invoice items from order items
             */
            foreach ($order->items as $item) {
                InvoiceItem::create([
                    'invoice_id'     => $invoice->id,
                    'order_item_id'  => $item->id,
                    'item_name'      => $item->product->title,
                    'quantity'       => $item->quantity,
                    'unit_price'     => $item->unit_price,
                    'subtotal'       => $item->subtotal,
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(): string
    {
        return 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}
