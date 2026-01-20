<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

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
                    '<code>'.e($row->invoice_number).'</code>'
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
                    strtoupper($row->currency) . ' ' . number_format($row->subtotal, 2)
                )

                ->editColumn('grand_total', fn ($row) =>
                    '<strong>' .
                    strtoupper($row->currency) . ' ' . number_format($row->grand_total, 2) .
                    '</strong>'
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
            'order:id,order_number,currency'
        ]);

        return view('content.invoices.show', compact('invoice'));
    }

    /**
     * Generate invoice manually from order (ADMIN ONLY – optional)
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
        $invoice->load([
            'items.orderItem.product',
            'user',
            'order'
        ]);

        // ✅ Billing address snapshot
        $billing = $invoice->order->billingAddress;



        // 1️⃣ Load pure HTML template
        $html = File::get(
            resource_path('views/content/invoices/print.html')
        );

        // 2️⃣ Build invoice items rows
        $itemsHtml = '';

        foreach ($invoice->items as $item) {
            $itemsHtml .= '
            <tr>
                <td>' . e($item->item_name) . '</td>
                <td>' . e($item->orderItem->product->title ?? '-') . '</td>
                <td>' . strtoupper($invoice->currency) . ' ' . number_format($item->unit_price, 2) . '</td>
                <td>' . $item->quantity . '</td>
                <td>' . strtoupper($invoice->currency) . ' ' . number_format($item->subtotal, 2) . '</td>
            </tr>';
        }

        // 3️⃣ Replace ALL placeholders (USING billingAddress())
        $html = str_replace([
            '{{company_name}}',
            '{{company_address}}',
            '{{company_location}}',
            '{{company_phone}}',

            '{{invoice_number}}',
            '{{issued_date}}',
            '{{due_date}}',

            '{{customer_name}}',
            '{{customer_company}}',
            '{{customer_address}}',
            '{{customer_phone}}',
            '{{customer_email}}',

            '{{currency}}',
            '{{subtotal}}',
            '{{discount}}',
            '{{tax}}',
            '{{grand_total}}',

            '{{items}}',

            '{{salesperson}}',
            '{{footer_message}}',
            '{{note}}',
        ], [
            // Company info
            config('app.name'),
            'Office 149, 450 South Brand Brooklyn',
            'San Diego County, CA 91905, USA',
            '+1 (123) 456 7891',

            // Invoice info
            $invoice->invoice_number,
            $invoice->issued_at->format('d M Y'),
            optional($invoice->due_at)->format('d M Y') ?? '-',

            // ✅ CUSTOMER (FROM billingAddress())
            $billing->name ?? $invoice->user->name,
            $billing->company ?? '-',
            $billing->address ?? '-',
            $billing->phone ?? '-',
            $billing->email ?? $invoice->user->email,

            // Totals
            strtoupper($invoice->currency),
            number_format($invoice->subtotal, 2),
            number_format($invoice->discount_total, 2),
            number_format($invoice->tax_total, 2),
            number_format($invoice->grand_total, 2),

            // Items
            $itemsHtml,

            // Footer
            auth()->user()->name ?? '-',
            'Thanks for your business',
            $invoice->meta['note'] ?? '-',
        ], $html);

        // 4️⃣ Return rendered HTML
        return response($html);
    }




    public function download(Invoice $invoice)
    {
        $invoice->load([
            'items.orderItem.product',
            'user',
            'order'
        ]);

        // ✅ Correct hasOne access
        $billing = $invoice->order->billingAddress;

        // 1️⃣ Load pure HTML template
        $html = File::get(
            resource_path('views/content/invoices/print.html')
        );

        // 2️⃣ Build invoice items rows
        $itemsHtml = '';

        foreach ($invoice->items as $item) {
            $itemsHtml .= '
            <tr>
                <td>' . e($item->item_name) . '</td>
                <td>' . e($item->orderItem->product->title ?? '-') . '</td>
                <td>' . strtoupper($invoice->currency) . ' ' . number_format($item->unit_price, 2) . '</td>
                <td>' . $item->quantity . '</td>
                <td>' . strtoupper($invoice->currency) . ' ' . number_format($item->subtotal, 2) . '</td>
            </tr>';
        }

        // 3️⃣ Replace ALL placeholders (same as print)
        $html = str_replace([
            '{{company_name}}',
            '{{company_address}}',
            '{{company_location}}',
            '{{company_phone}}',

            '{{invoice_number}}',
            '{{issued_date}}',
            '{{due_date}}',

            '{{customer_name}}',
            '{{customer_company}}',
            '{{customer_address}}',
            '{{customer_phone}}',
            '{{customer_email}}',

            '{{currency}}',
            '{{subtotal}}',
            '{{discount}}',
            '{{tax}}',
            '{{grand_total}}',

            '{{items}}',

            '{{salesperson}}',
            '{{footer_message}}',
            '{{note}}',
        ], [
            // Company
            config('app.name'),
            'Office 149, 450 South Brand Brooklyn',
            'San Diego County, CA 91905, USA',
            '+1 (123) 456 7891',

            // Invoice
            $invoice->invoice_number,
            $invoice->issued_at->format('d M Y'),
            optional($invoice->due_at)->format('d M Y') ?? '-',

            // ✅ Customer (billing address snapshot)
            $billing?->name ?? $invoice->user->name,
            $billing?->company ?? '-',
            $billing?->address ?? '-',
            $billing?->phone ?? '-',
            $billing?->email ?? $invoice->user->email,

            // Totals
            strtoupper($invoice->currency),
            number_format($invoice->subtotal, 2),
            number_format($invoice->discount_total, 2),
            number_format($invoice->tax_total, 2),
            number_format($invoice->grand_total, 2),

            // Items
            $itemsHtml,

            // Footer
            auth()->user()->name ?? '-',
            'Thanks for your business',
            $invoice->meta['note'] ?? '-',
        ], $html);

        // 4️⃣ Generate PDF from HTML
        $pdf = Pdf::loadHTML($html)->setPaper('a4');

        return $pdf->download(
            'Invoice-' . $invoice->invoice_number . '.pdf'
        );
    }


    /**
     * Mark invoice as paid (admin override)
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
