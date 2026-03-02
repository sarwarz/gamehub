<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * Generate the invoice PDF as raw string content for email attachment.
     * Returns [filename, pdfContent] or null if no invoice exists.
     */
    public static function getPdfForOrder(Order $order): ?array
    {
        $order->loadMissing('invoice');
        $invoice = $order->invoice;
        if (!$invoice) return null;

        try {
            $html = self::renderHtml($invoice);
            $pdf = Pdf::loadHTML($html)->setPaper('a4');
            $filename = 'Invoice-' . $invoice->invoice_number . '.pdf';
            return [$filename, $pdf->output()];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Invoice PDF generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Render invoice HTML (shared between controller download and email attachment).
     */
    public static function renderHtml(Invoice $invoice): string
    {
        $invoice->loadMissing([
            'items.orderItem.product',
            'user',
            'order.billingAddress',
        ]);

        $billing = $invoice->order?->billingAddress;

        $html = File::get(resource_path('views/content/invoices/print.html'));

        $invoiceCurrency = $invoice->currency;

        $itemsHtml = '';
        $idx = 0;
        foreach ($invoice->items as $item) {
            $idx++;
            $itemsHtml .= '<tr>'
                . '<td class="text-center">' . $idx . '</td>'
                . '<td><strong>' . e($item->item_name) . '</strong></td>'
                . '<td class="text-right">' . format_currency($item->unit_price, $invoiceCurrency) . '</td>'
                . '<td class="text-center">' . $item->quantity . '</td>'
                . '<td class="text-right fw-bold">' . format_currency($item->subtotal, $invoiceCurrency) . '</td>'
                . '</tr>';
        }

        $addressLines = collect([
            e($billing?->address),
            e(implode(', ', array_filter([$billing?->city, $billing?->state, $billing?->postal_code]))),
            e($billing?->country),
            $billing?->phone ? 'Phone: ' . e($billing->phone) : null,
            'Email: ' . e($billing?->email ?? $invoice->user?->email ?? ''),
        ])->filter()->implode('<br>');

        $statusClass = match ($invoice->status) {
            'paid' => 'paid', 'issued' => 'issued', 'cancelled' => 'cancelled', default => 'draft',
        };

        $dueDateLine = optional($invoice->due_at)->format('d M Y')
            ? 'Due: ' . $invoice->due_at->format('d M Y') . '<br>'
            : '';

        $noteBlock = '';
        $note = $invoice->meta['note'] ?? null;
        if ($note) {
            $noteBlock = '<div class="section-title">Note</div>'
                . '<p style="font-size:11px;color:#4a5568;font-style:italic">' . e($note) . '</p>';
        }

        $paidWatermark = $invoice->status === 'paid' ? '<div class="paid-stamp">PAID</div>' : '';

        $companyName    = Setting::get('invoice', 'company_name', config('app.name'));
        $companyAddress = Setting::get('invoice', 'company_address', '');
        $taxNumber      = Setting::get('invoice', 'tax_number', '');
        $footerNote     = Setting::get('invoice', 'footer_note', 'Thank you for your business!');

        $taxNumberHtml = $taxNumber
            ? '<div style="font-size:10px;opacity:.85;margin-top:2px">Tax No: ' . e($taxNumber) . '</div>'
            : '';

        return str_replace([
            '{{app_name}}', '{{app_address}}', '{{tax_number}}',
            '{{invoice_number}}', '{{issued_date}}', '{{due_date_line}}',
            '{{status_class}}', '{{status_label}}', '{{paid_watermark}}',
            '{{customer_name}}', '{{customer_address_lines}}',
            '{{order_number}}', '{{payment_method}}', '{{currency}}',
            '{{subtotal}}', '{{discount}}', '{{tax}}', '{{grand_total}}',
            '{{items}}', '{{note_block}}',
            '{{salesperson}}', '{{footer_message}}',
        ], [
            e($companyName), e($companyAddress), $taxNumberHtml,
            $invoice->invoice_number, $invoice->issued_at->format('d M Y'), $dueDateLine,
            $statusClass, strtoupper($invoice->status), $paidWatermark,
            e($billing?->name ?? $invoice->user?->name ?? 'Customer'), $addressLines,
            $invoice->order?->order_number ?? '-', ucfirst($invoice->order?->payment_method ?? 'N/A'), strtoupper($invoiceCurrency ?? 'USD'),
            format_currency($invoice->subtotal, $invoiceCurrency), format_currency($invoice->discount_total, $invoiceCurrency), format_currency($invoice->tax_total, $invoiceCurrency), format_currency($invoice->grand_total, $invoiceCurrency),
            $itemsHtml, $noteBlock,
            e($companyName), e($footerNote),
        ], $html);
    }

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
            $taxTotal      = (float) ($order->tax_amount ?? 0);
            $discountTotal = (float) ($order->discount_amount ?? 0);
            $grandTotal    = (float) ($order->total_amount ?? $subtotal);

            $invoice = Invoice::create([
                'order_id'       => $order->id,
                'user_id'        => $order->user_id,
                'invoice_number' => $this->generateInvoiceNumber(),

                'issued_at'      => now(),
                'paid_at'        => now(),

                'subtotal'       => $subtotal,
                'tax_total'      => $taxTotal,
                'discount_total' => $discountTotal,
                'grand_total'    => $grandTotal,
                'currency'       => $order->currency,

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
        $prefix = Setting::get('invoice', 'prefix', 'INV');
        return $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}
