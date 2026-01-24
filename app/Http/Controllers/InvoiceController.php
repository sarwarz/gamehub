<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    /**
     * List invoices (DataTable)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Invoice::with([
                'user:id,name,email',
                'order:id,order_number'
            ])->select('invoices.*');

            return DataTables::of($query)
                ->addIndexColumn()

                ->editColumn('invoice_number', fn ($row) =>
                    '<code>' . e($row->invoice_number) . '</code>'
                )

                ->addColumn('customer', function ($row) {
                    return '<div>
                        <strong>' . e($row->user->name ?? '-') . '</strong><br>
                        <small class="text-muted">' . e($row->user->email ?? '') . '</small>
                    </div>';
                })

                ->addColumn('order', fn ($row) =>
                    e($row->order->order_number ?? '-')
                )

                ->editColumn('issued_at', fn ($row) =>
                    optional($row->issued_at)->format('d M Y') ?? '-'
                )

                ->editColumn('paid_at', fn ($row) =>
                    $row->paid_at
                        ? $row->paid_at->format('d M Y')
                        : '<span class="text-muted">—</span>'
                )

                ->editColumn('subtotal', fn ($row) =>
                    format_currency($row->subtotal)
                )

                ->editColumn('grand_total', fn ($row) =>
                    '<strong>' . format_currency($row->grand_total) . '</strong>'
                )

                ->editColumn('status', function ($row) {
                    $map = [
                        'draft'     => 'secondary',
                        'issued'    => 'info',
                        'paid'      => 'success',
                        'cancelled' => 'danger',
                    ];

                    return '<span class="badge bg-' . ($map[$row->status] ?? 'secondary') . '">' .
                        strtoupper($row->status) .
                        '</span>';
                })

                ->addColumn('action', fn ($row) =>
                    view('content.invoices.partials.actions', [
                        'invoice' => $row
                    ])->render()
                )

                ->rawColumns([
                    'invoice_number',
                    'customer',
                    'paid_at',
                    'grand_total',
                    'status',
                    'action'
                ])
                ->make(true);
        }

        return view('content.invoices.index');
    }

    /**
     * Show invoice
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'items.orderItem.product:id,title',
            'user:id,name,email',
            'order:id,order_number'
        ]);

        return view('content.invoices.show', compact('invoice'));
    }

    /**
     * Generate invoice manually from order
     */
    public function generateFromOrder(Order $order, InvoiceService $service)
    {
        $invoice = $service->generateFromOrder($order);

        return redirect()
            ->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice generated successfully.');
    }

    /**
     * Print invoice (browser print)
     */
    public function print(Invoice $invoice)
    {
        return $this->renderInvoiceHtml($invoice);
    }

    /**
     * Download invoice PDF
     */
    public function download(Invoice $invoice)
    {
        $html = $this->renderInvoiceHtml($invoice, true);

        $pdf = Pdf::loadHTML($html)->setPaper('a4');

        return $pdf->download('Invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Shared HTML renderer (PRINT + PDF)
     */
    private function renderInvoiceHtml(Invoice $invoice, bool $returnHtmlOnly = false)
    {
        $invoice->load([
            'items.orderItem.product',
            'user',
            'order.billingAddress'
        ]);

        $billing = $invoice->order->billingAddress;

        $html = File::get(
            resource_path('views/content/invoices/print.html')
        );

        // Build items rows
        $itemsHtml = '';
        foreach ($invoice->items as $item) {
            $itemsHtml .= '
            <tr>
                <td>' . e($item->item_name) . '</td>
                <td>' . e($item->orderItem->product->title ?? '-') . '</td>
                <td>' . format_currency($item->unit_price) . '</td>
                <td>' . $item->quantity . '</td>
                <td>' . format_currency($item->subtotal) . '</td>
            </tr>';
        }

        // Replace placeholders
        $html = str_replace([
            '{{invoice_number}}',
            '{{issued_date}}',
            '{{due_date}}',

            '{{customer_name}}',
            '{{customer_company}}',
            '{{customer_address}}',
            '{{customer_phone}}',
            '{{customer_email}}',

            '{{subtotal}}',
            '{{discount}}',
            '{{tax}}',
            '{{grand_total}}',

            '{{items}}',

            '{{salesperson}}',
            '{{footer_message}}',
            '{{note}}',
        ], [
            $invoice->invoice_number,
            $invoice->issued_at->format('d M Y'),
            optional($invoice->due_at)->format('d M Y') ?? '-',

            $billing?->name ?? $invoice->user->name,
            $billing?->company ?? '-',
            $billing?->address ?? '-',
            $billing?->phone ?? '-',
            $billing?->email ?? $invoice->user->email,

            format_currency($invoice->subtotal),
            format_currency($invoice->discount_total),
            format_currency($invoice->tax_total),
            format_currency($invoice->grand_total),

            $itemsHtml,

            auth()->user()->name ?? '-',
            'Thanks for your business',
            $invoice->meta['note'] ?? '-',
        ], $html);

        return $returnHtmlOnly ? $html : response($html);
    }

    /**
     * Generate invoice from order
     */
    public function generate(Order $order)
    {
        if ($order->invoice) {
            return back()->with('warning', 'Invoice already exists.');
        }

        DB::transaction(function () use ($order) {
            $invoice = Invoice::create([
                'order_id'       => $order->id,
                'user_id'        => $order->user_id,
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . $order->id,
                'subtotal'       => $order->subtotal,
                'tax_total'      => $order->tax_total ?? 0,
                'discount_total' => $order->discount_total ?? 0,
                'grand_total'    => $order->total_amount,
                'currency'       => $order->currency,
                'status'         => 'paid',
                'issued_at'      => now(),
            ]);

            foreach ($order->items as $item) {
                $invoice->items()->create([
                    'order_item_id' => $item->id,
                    'item_name'     => $item->product->title,
                    'quantity'      => $item->quantity,
                    'unit_price'    => $item->unit_price,
                    'subtotal'      => $item->subtotal,
                ]);
            }
        });

        return back()->with('success', 'Invoice generated successfully.');
    }

    /**
     * Mark invoice as paid
     */
    public function markPaid(Invoice $invoice)
    {
        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Invoice marked as paid.');
    }

    /**
     * Delete invoice
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return back()->with('success', 'Invoice deleted successfully.');
    }
}
