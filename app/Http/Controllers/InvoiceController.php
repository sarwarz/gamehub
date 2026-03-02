<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('checkbox', fn ($row) =>
                    '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$row->id.'">'
                )

                ->editColumn('invoice_number', fn ($row) =>
                    '<a href="'.route('invoices.show', $row->id).'" class="fw-semibold text-body"><code>'.e($row->invoice_number).'</code></a>'
                )

                ->addColumn('customer', function ($row) {
                    $avatar = $row->user->avatar_url ?? asset('assets/img/avatars/1.png');
                    return '<div class="d-flex align-items-center"><img src="'.$avatar.'" class="rounded-circle me-2" width="32" height="32" style="object-fit:cover"><div class="lh-sm"><span class="fw-semibold d-block">'.e($row->user->name ?? '-').'</span><small class="text-muted">'.e($row->user->email ?? '').'</small></div></div>';
                })

                ->addColumn('order', fn ($row) =>
                    $row->order ? '<a href="'.route('orders.edit', $row->order->id).'" class="fw-semibold text-body">#'.e($row->order->order_number).'</a>' : '<span class="text-muted">—</span>'
                )

                ->editColumn('issued_at', fn ($row) =>
                    '<span class="small">'.optional($row->issued_at)->format('M d, Y').'</span>' ?? '-'
                )

                ->editColumn('paid_at', fn ($row) =>
                    $row->paid_at
                        ? '<span class="small text-success">'.($row->paid_at->format('M d, Y')).'</span>'
                        : '<span class="text-muted">—</span>'
                )

                ->editColumn('subtotal', fn ($row) =>
                    format_currency($row->subtotal)
                )

                ->editColumn('grand_total', fn ($row) =>
                    '<span class="fw-bold text-primary">'.format_currency($row->grand_total).'</span>'
                )

                ->editColumn('status', function ($row) {
                    $map = ['draft' => 'secondary', 'issued' => 'info', 'paid' => 'success', 'cancelled' => 'danger'];
                    return '<span class="badge bg-label-'.($map[$row->status] ?? 'secondary').'">'.ucfirst($row->status).'</span>';
                })

                ->addColumn('action', fn ($row) =>
                    '<div class="d-flex align-items-center justify-content-center gap-1">
                        <a href="'.route('invoices.show', $row->id).'" class="btn btn-icon btn-sm btn-label-info" title="View">
                            <i class="ti tabler-eye ti-xs"></i>
                        </a>
                        <a href="'.route('invoices.download', $row->id).'" class="btn btn-icon btn-sm btn-label-primary" title="Download PDF">
                            <i class="ti tabler-download ti-xs"></i>
                        </a>
                        <button type="button" class="btn btn-icon btn-sm btn-label-danger delete-btn" data-url="'.route('invoices.destroy', $row->id).'" title="Delete">
                            <i class="ti tabler-trash ti-xs"></i>
                        </button>
                    </div>'
                )

                ->rawColumns([
                    'checkbox', 'invoice_number', 'customer', 'order',
                    'issued_at', 'paid_at', 'grand_total', 'status', 'action'
                ])
                ->make(true);
        }

        $stats = [
            'total'    => Invoice::count(),
            'paid'     => Invoice::where('status', 'paid')->count(),
            'unpaid'   => Invoice::where('status', 'unpaid')->count(),
            'overdue'  => Invoice::where('status', 'overdue')->count(),
            'revenue'  => Invoice::where('status', 'paid')->sum('grand_total'),
        ];

        return view('content.invoices.index', compact('stats'));
    }

    /**
     * Show invoice
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'items.orderItem.product:id,title',
            'user:id,name,email',
            'order:id,order_number,payment_method,currency,status',
            'order.billingAddress',
        ]);

        $billing = $invoice->order?->billingAddress;

        return view('content.invoices.show', compact('invoice', 'billing'));
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
        return response(InvoiceService::renderHtml($invoice));
    }

    /**
     * Download invoice PDF
     */
    public function download(Invoice $invoice)
    {
        $html = InvoiceService::renderHtml($invoice);
        $pdf = Pdf::loadHTML($html)->setPaper('a4');
        return $pdf->download('Invoice-' . $invoice->invoice_number . '.pdf');
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
                'invoice_number' => Setting::get('invoice', 'prefix', 'INV') . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'subtotal'       => $order->subtotal,
                'tax_total'      => $order->tax_amount ?? 0,
                'discount_total' => $order->discount_amount ?? 0,
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
     * Bulk delete invoices
     */
    public function bulkDelete(Request $request)
    {

        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:invoices,id']);
        Invoice::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => 'Invoices deleted successfully.']);
    }

    /**
     * Delete invoice
     */
    public function destroy(Invoice $invoice)
    {

        $invoice->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Invoice deleted successfully.']);
        }

        return back()->with('success', 'Invoice deleted successfully.');
    }
}
