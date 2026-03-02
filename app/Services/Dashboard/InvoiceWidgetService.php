<?php

namespace App\Services\Dashboard;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class InvoiceWidgetService
{
    public function data($user): array
    {
        return Cache::remember('dashboard:invoices:admin', now()->addMinutes(5), fn () => $this->build());
    }

    protected function build(): array
    {
        $invoices = Invoice::with(['user:id,name,email', 'order:id,order_number'])
            ->latest('issued_at')
            ->limit(8)
            ->get();

        $statusCounts = Invoice::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'invoices' => $invoices->map(fn ($inv) => [
                'id'               => $inv->id,
                'invoice_number'   => $inv->invoice_number,
                'customer_name'    => $inv->user?->name ?? 'Guest',
                'customer_email'   => $inv->user?->email,
                'order_number'     => $inv->order?->order_number,
                'grand_total'      => round((float) $inv->grand_total, 2),
                'formatted_total'  => format_currency($inv->grand_total),
                'status'           => $inv->status,
                'issued_at'        => $inv->issued_at?->format('M d, Y'),
                'paid_at'          => $inv->paid_at?->format('M d, Y'),
            ])->toArray(),
            'counts' => [
                'paid'            => $statusCounts['paid'] ?? 0,
                'unpaid'          => $statusCounts['unpaid'] ?? 0,
                'partially_paid'  => $statusCounts['partially_paid'] ?? 0,
                'draft'           => $statusCounts['draft'] ?? 0,
                'cancelled'       => $statusCounts['cancelled'] ?? 0,
                'total'           => array_sum($statusCounts),
            ],
            'show_url' => route('invoices.index'),
        ];
    }
}
