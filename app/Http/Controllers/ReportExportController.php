<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\Setting;
use App\Jobs\ProcessReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    protected const PDF_MAX_ROWS = 5000;
    protected const FULL_PDF_SECTION_LIMIT = 1000;
    protected const LAZY_CHUNK = 1000;

    // ─── Shared helpers ──────────────────────────────

    private function dateRange(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : now()->subDays(29)->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }

    private function format(Request $request): string
    {
        return in_array($request->format, ['csv', 'pdf']) ? $request->format : 'csv';
    }

    private function rangeLabel(Carbon $from, Carbon $to): string
    {
        return $from->format('M d, Y') . ' — ' . $to->format('M d, Y');
    }

    private function csvFilename(string $title): string
    {
        return str_replace(' ', '_', strtolower($title)) . '_' . now()->format('Ymd_His') . '.csv';
    }

    // ─── Streaming CSV (memory-safe) ─────────────────

    private function streamCsv(string $filename, array $headings, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows) {
            if (ob_get_level()) ob_end_clean();
            set_time_limit(0);

            $out = fopen('php://output', 'w');
            fputcsv($out, $headings);

            $count = 0;
            foreach ($rows as $row) {
                fputcsv($out, $row);
                if (++$count % 1000 === 0) {
                    flush();
                }
            }

            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ─── PDF with row cap ────────────────────────────

    private function generatePdf(
        string $title,
        string $rangeLabel,
        array  $headings,
        array  $rows,
        array  $summary = [],
        ?int   $totalCount = null
    ) {
        $truncated = $totalCount !== null && $totalCount > count($rows);

        $pdf = Pdf::loadView('exports.report-pdf', [
            'title'        => $title,
            'rangeLabel'   => $rangeLabel,
            'headings'     => $headings,
            'rows'         => $rows,
            'summary'      => $summary,
            'appName'      => Setting::get('general', 'site_name', config('app.name')),
            'generatedAt'  => now()->format('M d, Y h:i A'),
            'truncated'    => $truncated,
            'truncatedMsg' => $truncated
                ? 'Showing first ' . number_format(count($rows)) . ' of ' . number_format($totalCount) . ' rows. Export as CSV for complete data.'
                : null,
        ])->setPaper('a4', 'landscape');

        $filename = str_replace(' ', '_', strtolower($title)) . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    // ─── Data generators (stream row-by-row) ─────────
    // Each returns [headings, Generator] — constant memory regardless of dataset size

    private function iterateSales(Carbon $from, Carbon $to): array
    {
        $headings = ['Order #', 'Customer', 'Email', 'Amount', 'Status', 'Payment', 'Gateway', 'Date'];
        $rows = (function () use ($from, $to) {
            foreach (Order::whereBetween('created_at', [$from, $to])
                ->with('user:id,name,email')
                ->orderByDesc('created_at')
                ->lazy(self::LAZY_CHUNK) as $o) {
                yield [
                    $o->order_number,
                    $o->user->name ?? '—',
                    $o->user->email ?? '—',
                    number_format($o->total_amount, 2),
                    $o->status,
                    $o->payment_status,
                    $o->payment_method ?? '—',
                    $o->created_at->format('Y-m-d H:i'),
                ];
            }
        })();
        return [$headings, $rows];
    }

    private function iterateRevenue(Carbon $from, Carbon $to): array
    {
        $headings = ['Seller', 'Order #', 'Gross', 'Commission', 'Net', 'Status', 'Date'];
        $rows = (function () use ($from, $to) {
            foreach (DB::table('seller_earnings')
                ->join('sellers', 'sellers.id', '=', 'seller_earnings.seller_id')
                ->join('orders', 'orders.id', '=', 'seller_earnings.order_id')
                ->selectRaw('sellers.store_name, orders.order_number, seller_earnings.gross_amount, seller_earnings.commission, seller_earnings.net_amount, seller_earnings.status, seller_earnings.created_at')
                ->whereBetween('seller_earnings.created_at', [$from, $to])
                ->orderByDesc('seller_earnings.created_at')
                ->cursor() as $e) {
                yield [
                    $e->store_name,
                    $e->order_number,
                    number_format($e->gross_amount, 2),
                    number_format($e->commission, 2),
                    number_format($e->net_amount, 2),
                    $e->status,
                    Carbon::parse($e->created_at)->format('Y-m-d'),
                ];
            }
        })();
        return [$headings, $rows];
    }

    private function iterateProducts(Carbon $from, Carbon $to): array
    {
        $headings = ['Product', 'Units Sold', 'Revenue', 'Avg Price'];
        $rows = (function () use ($from, $to) {
            foreach (DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->selectRaw('products.title, SUM(order_items.quantity) as units, SUM(order_items.subtotal) as revenue, AVG(order_items.unit_price) as avg_price')
                ->whereBetween('orders.created_at', [$from, $to])
                ->where('orders.payment_status', 'paid')
                ->groupBy('products.id', 'products.title')
                ->orderByDesc('revenue')
                ->cursor() as $p) {
                yield [
                    $p->title,
                    (int) $p->units,
                    number_format($p->revenue, 2),
                    number_format($p->avg_price, 2),
                ];
            }
        })();
        return [$headings, $rows];
    }

    private function iterateCustomers(Carbon $from, Carbon $to): array
    {
        $headings = ['Name', 'Email', 'Orders', 'Total Spent', 'Avg Order', 'Last Order'];
        $rows = (function () use ($from, $to) {
            foreach (DB::table('orders')
                ->join('users', 'users.id', '=', 'orders.user_id')
                ->selectRaw('users.name, users.email, COUNT(orders.id) as orders_count, SUM(orders.total_amount) as total_spent, AVG(orders.total_amount) as avg_order, MAX(orders.created_at) as last_order')
                ->whereBetween('orders.created_at', [$from, $to])
                ->where('orders.payment_status', 'paid')
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderByDesc('total_spent')
                ->cursor() as $c) {
                yield [
                    $c->name,
                    $c->email,
                    $c->orders_count,
                    number_format($c->total_spent, 2),
                    number_format($c->avg_order, 2),
                    Carbon::parse($c->last_order)->format('Y-m-d'),
                ];
            }
        })();
        return [$headings, $rows];
    }

    private function iterateSellers(Carbon $from, Carbon $to): array
    {
        $headings = ['Store', 'Rating', 'Orders', 'Gross Revenue', 'Commission', 'Net Earnings'];
        $rows = (function () use ($from, $to) {
            foreach (DB::table('seller_earnings')
                ->join('sellers', 'sellers.id', '=', 'seller_earnings.seller_id')
                ->selectRaw('sellers.store_name, sellers.rating, COUNT(DISTINCT seller_earnings.order_id) as orders_count, SUM(seller_earnings.gross_amount) as revenue, SUM(seller_earnings.commission) as commission, SUM(seller_earnings.net_amount) as net')
                ->whereBetween('seller_earnings.created_at', [$from, $to])
                ->groupBy('sellers.id', 'sellers.store_name', 'sellers.rating')
                ->orderByDesc('revenue')
                ->cursor() as $s) {
                yield [
                    $s->store_name,
                    $s->rating,
                    $s->orders_count,
                    number_format($s->revenue, 2),
                    number_format($s->commission, 2),
                    number_format($s->net, 2),
                ];
            }
        })();
        return [$headings, $rows];
    }

    private function iteratePayments(Carbon $from, Carbon $to): array
    {
        $headings = ['Seller', 'Method', 'Amount', 'Status', 'Date'];
        $rows = (function () use ($from, $to) {
            foreach (DB::table('seller_withdraws')
                ->join('sellers', 'sellers.id', '=', 'seller_withdraws.seller_id')
                ->selectRaw('sellers.store_name, seller_withdraws.method, seller_withdraws.amount, seller_withdraws.status, seller_withdraws.created_at')
                ->whereBetween('seller_withdraws.created_at', [$from, $to])
                ->orderByDesc('seller_withdraws.created_at')
                ->cursor() as $w) {
                yield [
                    $w->store_name,
                    $w->method,
                    number_format($w->amount, 2),
                    $w->status,
                    Carbon::parse($w->created_at)->format('Y-m-d'),
                ];
            }
        })();
        return [$headings, $rows];
    }

    private function iterateRefunds(Carbon $from, Carbon $to): array
    {
        $headings = ['Order #', 'Customer', 'Seller', 'Amount', 'Reason', 'Status', 'Date'];
        $rows = (function () use ($from, $to) {
            foreach (DB::table('refund_requests')
                ->join('orders', 'orders.id', '=', 'refund_requests.order_id')
                ->join('users', 'users.id', '=', 'refund_requests.user_id')
                ->leftJoin('sellers', 'sellers.id', '=', 'refund_requests.seller_id')
                ->selectRaw('orders.order_number, users.name as customer, sellers.store_name as seller, refund_requests.amount, refund_requests.reason, refund_requests.status, refund_requests.created_at')
                ->whereBetween('refund_requests.created_at', [$from, $to])
                ->orderByDesc('refund_requests.created_at')
                ->cursor() as $r) {
                yield [
                    $r->order_number,
                    $r->customer,
                    $r->seller ?? '—',
                    number_format($r->amount, 2),
                    $r->reason,
                    $r->status,
                    Carbon::parse($r->created_at)->format('Y-m-d'),
                ];
            }
        })();
        return [$headings, $rows];
    }

    private function iterateSupport(Carbon $from, Carbon $to): array
    {
        $headings = ['Ticket #', 'Customer', 'Subject', 'Priority', 'Department', 'Status', 'Created', 'Resolved'];
        $rows = (function () use ($from, $to) {
            foreach (SupportTicket::whereBetween('created_at', [$from, $to])
                ->with('user:id,name')
                ->orderByDesc('created_at')
                ->lazy(self::LAZY_CHUNK) as $t) {
                yield [
                    $t->ticket_number,
                    $t->user->name ?? '—',
                    $t->subject,
                    $t->priority,
                    $t->department ?? 'general',
                    $t->status,
                    $t->created_at->format('Y-m-d'),
                    $t->resolved_at ? $t->resolved_at->format('Y-m-d') : '—',
                ];
            }
        })();
        return [$headings, $rows];
    }

    // ─── PDF data fetchers (limited, with accurate totals) ───

    private function pdfSalesData(Carbon $from, Carbon $to): array
    {
        $query = Order::whereBetween('created_at', [$from, $to])
            ->with('user:id,name,email')
            ->orderByDesc('created_at');

        $totalCount = (clone $query)->count();
        $rows = (clone $query)->limit(self::PDF_MAX_ROWS)->get()->map(fn($o) => [
            $o->order_number, $o->user->name ?? '—', $o->user->email ?? '—',
            number_format($o->total_amount, 2), $o->status, $o->payment_status,
            $o->payment_method ?? '—', $o->created_at->format('Y-m-d H:i'),
        ])->toArray();

        $summary = [
            'Total Orders'  => number_format($totalCount),
            'Total Revenue' => '$' . number_format(
                Order::whereBetween('created_at', [$from, $to])->where('payment_status', 'paid')->sum('total_amount'), 2
            ),
        ];

        return [$rows, $summary, $totalCount];
    }

    private function pdfRevenueData(Carbon $from, Carbon $to): array
    {
        $query = DB::table('seller_earnings')
            ->join('sellers', 'sellers.id', '=', 'seller_earnings.seller_id')
            ->join('orders', 'orders.id', '=', 'seller_earnings.order_id')
            ->selectRaw('sellers.store_name, orders.order_number, seller_earnings.gross_amount, seller_earnings.commission, seller_earnings.net_amount, seller_earnings.status, seller_earnings.created_at')
            ->whereBetween('seller_earnings.created_at', [$from, $to])
            ->orderByDesc('seller_earnings.created_at');

        $totalCount = (clone $query)->count();
        $earnings = (clone $query)->limit(self::PDF_MAX_ROWS)->get();

        $rows = $earnings->map(fn($e) => [
            $e->store_name, $e->order_number,
            number_format($e->gross_amount, 2), number_format($e->commission, 2),
            number_format($e->net_amount, 2), $e->status,
            Carbon::parse($e->created_at)->format('Y-m-d'),
        ])->toArray();

        $allGross = (float) DB::table('seller_earnings')->whereBetween('created_at', [$from, $to])->sum('gross_amount');
        $allComm  = (float) DB::table('seller_earnings')->whereBetween('created_at', [$from, $to])->sum('commission');
        $allNet   = (float) DB::table('seller_earnings')->whereBetween('created_at', [$from, $to])->sum('net_amount');

        $summary = [
            'Gross Revenue'    => '$' . number_format($allGross, 2),
            'Total Commission' => '$' . number_format($allComm, 2),
            'Seller Net'       => '$' . number_format($allNet, 2),
        ];

        return [$rows, $summary, $totalCount];
    }

    private function pdfProductsData(Carbon $from, Carbon $to): array
    {
        $query = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('products.title, SUM(order_items.quantity) as units, SUM(order_items.subtotal) as revenue, AVG(order_items.unit_price) as avg_price')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('revenue');

        $totalCount = DB::query()->fromSub(clone $query, 'sub')->count();
        $rows = (clone $query)->limit(self::PDF_MAX_ROWS)->get()->map(fn($p) => [
            $p->title, (int) $p->units,
            number_format($p->revenue, 2), number_format($p->avg_price, 2),
        ])->toArray();

        $agg = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->selectRaw('SUM(order_items.quantity) as units, SUM(order_items.subtotal) as revenue')
            ->first();

        $summary = [
            'Total Units'   => number_format((int) ($agg->units ?? 0)),
            'Total Revenue' => '$' . number_format((float) ($agg->revenue ?? 0), 2),
        ];

        return [$rows, $summary, $totalCount];
    }

    private function pdfCustomersData(Carbon $from, Carbon $to): array
    {
        $query = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.name, users.email, COUNT(orders.id) as orders_count, SUM(orders.total_amount) as total_spent, AVG(orders.total_amount) as avg_order, MAX(orders.created_at) as last_order')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent');

        $totalCount = DB::query()->fromSub(clone $query, 'sub')->count();
        $rows = (clone $query)->limit(self::PDF_MAX_ROWS)->get()->map(fn($c) => [
            $c->name, $c->email, $c->orders_count,
            number_format($c->total_spent, 2), number_format($c->avg_order, 2),
            Carbon::parse($c->last_order)->format('Y-m-d'),
        ])->toArray();

        $summary = [
            'Total Customers' => number_format($totalCount),
            'Total Revenue'   => '$' . number_format(
                (float) Order::whereBetween('created_at', [$from, $to])->where('payment_status', 'paid')->sum('total_amount'), 2
            ),
        ];

        return [$rows, $summary, $totalCount];
    }

    private function pdfSellersData(Carbon $from, Carbon $to): array
    {
        $query = DB::table('seller_earnings')
            ->join('sellers', 'sellers.id', '=', 'seller_earnings.seller_id')
            ->selectRaw('sellers.store_name, sellers.rating, COUNT(DISTINCT seller_earnings.order_id) as orders_count, SUM(seller_earnings.gross_amount) as revenue, SUM(seller_earnings.commission) as commission, SUM(seller_earnings.net_amount) as net')
            ->whereBetween('seller_earnings.created_at', [$from, $to])
            ->groupBy('sellers.id', 'sellers.store_name', 'sellers.rating')
            ->orderByDesc('revenue');

        $totalCount = DB::query()->fromSub(clone $query, 'sub')->count();
        $rows = (clone $query)->limit(self::PDF_MAX_ROWS)->get()->map(fn($s) => [
            $s->store_name, $s->rating, $s->orders_count,
            number_format($s->revenue, 2), number_format($s->commission, 2),
            number_format($s->net, 2),
        ])->toArray();

        $summary = [
            'Total Sellers'  => number_format($totalCount),
            'Total Revenue'  => '$' . number_format(
                (float) DB::table('seller_earnings')->whereBetween('created_at', [$from, $to])->sum('gross_amount'), 2
            ),
        ];

        return [$rows, $summary, $totalCount];
    }

    private function pdfPaymentsData(Carbon $from, Carbon $to): array
    {
        $query = DB::table('seller_withdraws')
            ->join('sellers', 'sellers.id', '=', 'seller_withdraws.seller_id')
            ->selectRaw('sellers.store_name, seller_withdraws.method, seller_withdraws.amount, seller_withdraws.status, seller_withdraws.created_at')
            ->whereBetween('seller_withdraws.created_at', [$from, $to])
            ->orderByDesc('seller_withdraws.created_at');

        $totalCount = (clone $query)->count();
        $withdrawals = (clone $query)->limit(self::PDF_MAX_ROWS)->get();

        $rows = $withdrawals->map(fn($w) => [
            $w->store_name, $w->method,
            number_format($w->amount, 2), $w->status,
            Carbon::parse($w->created_at)->format('Y-m-d'),
        ])->toArray();

        $summary = [
            'Total Withdrawals' => number_format($totalCount),
            'Total Amount'      => '$' . number_format(
                (float) DB::table('seller_withdraws')->whereBetween('created_at', [$from, $to])->sum('amount'), 2
            ),
        ];

        return [$rows, $summary, $totalCount];
    }

    private function pdfRefundsData(Carbon $from, Carbon $to): array
    {
        $query = DB::table('refund_requests')
            ->join('orders', 'orders.id', '=', 'refund_requests.order_id')
            ->join('users', 'users.id', '=', 'refund_requests.user_id')
            ->leftJoin('sellers', 'sellers.id', '=', 'refund_requests.seller_id')
            ->selectRaw('orders.order_number, users.name as customer, sellers.store_name as seller, refund_requests.amount, refund_requests.reason, refund_requests.status, refund_requests.created_at')
            ->whereBetween('refund_requests.created_at', [$from, $to])
            ->orderByDesc('refund_requests.created_at');

        $totalCount = (clone $query)->count();
        $rows = (clone $query)->limit(self::PDF_MAX_ROWS)->get()->map(fn($r) => [
            $r->order_number, $r->customer, $r->seller ?? '—',
            number_format($r->amount, 2), $r->reason, $r->status,
            Carbon::parse($r->created_at)->format('Y-m-d'),
        ])->toArray();

        $summary = [
            'Total Refunds' => number_format($totalCount),
            'Total Amount'  => '$' . number_format(
                (float) DB::table('refund_requests')->whereBetween('created_at', [$from, $to])->sum('amount'), 2
            ),
        ];

        return [$rows, $summary, $totalCount];
    }

    private function pdfSupportData(Carbon $from, Carbon $to): array
    {
        $query = SupportTicket::whereBetween('created_at', [$from, $to])
            ->with('user:id,name,email')
            ->orderByDesc('created_at');

        $totalCount = (clone $query)->count();
        $tickets = (clone $query)->limit(self::PDF_MAX_ROWS)->get();

        $rows = $tickets->map(fn($t) => [
            $t->ticket_number, $t->user->name ?? '—', $t->subject,
            $t->priority, $t->department ?? 'general', $t->status,
            $t->created_at->format('Y-m-d'),
            $t->resolved_at ? $t->resolved_at->format('Y-m-d') : '—',
        ])->toArray();

        $summary = [
            'Total Tickets' => number_format($totalCount),
            'Resolved'      => number_format(
                SupportTicket::whereBetween('created_at', [$from, $to])->whereNotNull('resolved_at')->count()
            ),
        ];

        return [$rows, $summary, $totalCount];
    }

    // ─── Public endpoints ────────────────────────────

    public function sales(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if ($this->format($request) === 'pdf') {
            $headings = ['Order #', 'Customer', 'Email', 'Amount', 'Status', 'Payment', 'Gateway', 'Date'];
            [$rows, $summary, $totalCount] = $this->pdfSalesData($from, $to);
            return $this->generatePdf('Sales Report', $this->rangeLabel($from, $to), $headings, $rows, $summary, $totalCount);
        }

        [$headings, $rows] = $this->iterateSales($from, $to);
        return $this->streamCsv($this->csvFilename('Sales Report'), $headings, $rows);
    }

    public function revenue(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if ($this->format($request) === 'pdf') {
            $headings = ['Seller', 'Order #', 'Gross', 'Commission', 'Net', 'Status', 'Date'];
            [$rows, $summary, $totalCount] = $this->pdfRevenueData($from, $to);
            return $this->generatePdf('Revenue Report', $this->rangeLabel($from, $to), $headings, $rows, $summary, $totalCount);
        }

        [$headings, $rows] = $this->iterateRevenue($from, $to);
        return $this->streamCsv($this->csvFilename('Revenue Report'), $headings, $rows);
    }

    public function products(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if ($this->format($request) === 'pdf') {
            $headings = ['Product', 'Units Sold', 'Revenue', 'Avg Price'];
            [$rows, $summary, $totalCount] = $this->pdfProductsData($from, $to);
            return $this->generatePdf('Product Report', $this->rangeLabel($from, $to), $headings, $rows, $summary, $totalCount);
        }

        [$headings, $rows] = $this->iterateProducts($from, $to);
        return $this->streamCsv($this->csvFilename('Product Report'), $headings, $rows);
    }

    public function customers(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if ($this->format($request) === 'pdf') {
            $headings = ['Name', 'Email', 'Orders', 'Total Spent', 'Avg Order', 'Last Order'];
            [$rows, $summary, $totalCount] = $this->pdfCustomersData($from, $to);
            return $this->generatePdf('Customer Report', $this->rangeLabel($from, $to), $headings, $rows, $summary, $totalCount);
        }

        [$headings, $rows] = $this->iterateCustomers($from, $to);
        return $this->streamCsv($this->csvFilename('Customer Report'), $headings, $rows);
    }

    public function sellers(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if ($this->format($request) === 'pdf') {
            $headings = ['Store', 'Rating', 'Orders', 'Gross Revenue', 'Commission', 'Net Earnings'];
            [$rows, $summary, $totalCount] = $this->pdfSellersData($from, $to);
            return $this->generatePdf('Seller Report', $this->rangeLabel($from, $to), $headings, $rows, $summary, $totalCount);
        }

        [$headings, $rows] = $this->iterateSellers($from, $to);
        return $this->streamCsv($this->csvFilename('Seller Report'), $headings, $rows);
    }

    public function payments(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if ($this->format($request) === 'pdf') {
            $headings = ['Seller', 'Method', 'Amount', 'Status', 'Date'];
            [$rows, $summary, $totalCount] = $this->pdfPaymentsData($from, $to);
            return $this->generatePdf('Payment Report', $this->rangeLabel($from, $to), $headings, $rows, $summary, $totalCount);
        }

        [$headings, $rows] = $this->iteratePayments($from, $to);
        return $this->streamCsv($this->csvFilename('Payment Report'), $headings, $rows);
    }

    public function refunds(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if ($this->format($request) === 'pdf') {
            $headings = ['Order #', 'Customer', 'Seller', 'Amount', 'Reason', 'Status', 'Date'];
            [$rows, $summary, $totalCount] = $this->pdfRefundsData($from, $to);
            return $this->generatePdf('Refund Report', $this->rangeLabel($from, $to), $headings, $rows, $summary, $totalCount);
        }

        [$headings, $rows] = $this->iterateRefunds($from, $to);
        return $this->streamCsv($this->csvFilename('Refund Report'), $headings, $rows);
    }

    public function support(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if ($this->format($request) === 'pdf') {
            $headings = ['Ticket #', 'Customer', 'Subject', 'Priority', 'Department', 'Status', 'Created', 'Resolved'];
            [$rows, $summary, $totalCount] = $this->pdfSupportData($from, $to);
            return $this->generatePdf('Support Ticket Report', $this->rangeLabel($from, $to), $headings, $rows, $summary, $totalCount);
        }

        [$headings, $rows] = $this->iterateSupport($from, $to);
        return $this->streamCsv($this->csvFilename('Support Ticket Report'), $headings, $rows);
    }

    // ─── Full report ─────────────────────────────────

    public function full(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if ($this->format($request) === 'pdf') {
            return $this->fullPdf($from, $to);
        }

        return $this->fullCsv($from, $to);
    }

    private function fullCsv(Carbon $from, Carbon $to): StreamedResponse
    {
        $filename = 'full_report_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($from, $to) {
            if (ob_get_level()) ob_end_clean();
            set_time_limit(0);

            $out = fopen('php://output', 'w');
            $count = 0;

            $sections = [
                'SALES'     => fn() => $this->iterateSales($from, $to),
                'REVENUE'   => fn() => $this->iterateRevenue($from, $to),
                'PRODUCTS'  => fn() => $this->iterateProducts($from, $to),
                'CUSTOMERS' => fn() => $this->iterateCustomers($from, $to),
                'SELLERS'   => fn() => $this->iterateSellers($from, $to),
                'PAYMENTS'  => fn() => $this->iteratePayments($from, $to),
                'REFUNDS'   => fn() => $this->iterateRefunds($from, $to),
                'SUPPORT'   => fn() => $this->iterateSupport($from, $to),
            ];

            foreach ($sections as $label => $factory) {
                [$headings, $rows] = $factory();

                fputcsv($out, []);
                fputcsv($out, ["=== {$label} ==="]);
                fputcsv($out, $headings);

                foreach ($rows as $row) {
                    fputcsv($out, $row);
                    if (++$count % 1000 === 0) {
                        flush();
                    }
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function fullPdf(Carbon $from, Carbon $to)
    {
        $limit = self::FULL_PDF_SECTION_LIMIT;
        $rangeLabel = $this->rangeLabel($from, $to);

        $buildSection = function (string $title, array $headings, $query, callable $mapRow, bool $isGroupBy = false) use ($limit) {
            if ($isGroupBy) {
                $totalCount = DB::query()->fromSub(clone $query, 'sub')->count();
            } else {
                $totalCount = (clone $query)->count();
            }

            $rows = (clone $query)->limit($limit)->get()->map($mapRow)->toArray();
            $truncated = $totalCount > $limit;

            return [
                'title'        => $title,
                'headings'     => $headings,
                'rows'         => $rows,
                'truncated'    => $truncated,
                'truncatedMsg' => $truncated
                    ? 'Showing ' . number_format($limit) . ' of ' . number_format($totalCount) . ' rows'
                    : null,
            ];
        };

        $sections = [];

        $sections[] = $buildSection(
            'Sales',
            ['Order #', 'Customer', 'Email', 'Amount', 'Status', 'Payment', 'Gateway', 'Date'],
            Order::whereBetween('created_at', [$from, $to])->with('user:id,name,email')->orderByDesc('created_at'),
            fn($o) => [$o->order_number, $o->user->name ?? '—', $o->user->email ?? '—', number_format($o->total_amount, 2), $o->status, $o->payment_status, $o->payment_method ?? '—', $o->created_at->format('Y-m-d H:i')]
        );

        $sections[] = $buildSection(
            'Revenue',
            ['Seller', 'Order #', 'Gross', 'Commission', 'Net', 'Status', 'Date'],
            DB::table('seller_earnings')->join('sellers', 'sellers.id', '=', 'seller_earnings.seller_id')->join('orders', 'orders.id', '=', 'seller_earnings.order_id')
                ->selectRaw('sellers.store_name, orders.order_number, seller_earnings.gross_amount, seller_earnings.commission, seller_earnings.net_amount, seller_earnings.status, seller_earnings.created_at')
                ->whereBetween('seller_earnings.created_at', [$from, $to])->orderByDesc('seller_earnings.created_at'),
            fn($e) => [$e->store_name, $e->order_number, number_format($e->gross_amount, 2), number_format($e->commission, 2), number_format($e->net_amount, 2), $e->status, Carbon::parse($e->created_at)->format('Y-m-d')]
        );

        $sections[] = $buildSection(
            'Products',
            ['Product', 'Units Sold', 'Revenue', 'Avg Price'],
            DB::table('order_items')->join('orders', 'orders.id', '=', 'order_items.order_id')->join('products', 'products.id', '=', 'order_items.product_id')
                ->selectRaw('products.title, SUM(order_items.quantity) as units, SUM(order_items.subtotal) as revenue, AVG(order_items.unit_price) as avg_price')
                ->whereBetween('orders.created_at', [$from, $to])->where('orders.payment_status', 'paid')
                ->groupBy('products.id', 'products.title')->orderByDesc('revenue'),
            fn($p) => [$p->title, (int) $p->units, number_format($p->revenue, 2), number_format($p->avg_price, 2)],
            true
        );

        $sections[] = $buildSection(
            'Customers',
            ['Name', 'Email', 'Orders', 'Total Spent', 'Avg Order', 'Last Order'],
            DB::table('orders')->join('users', 'users.id', '=', 'orders.user_id')
                ->selectRaw('users.name, users.email, COUNT(orders.id) as orders_count, SUM(orders.total_amount) as total_spent, AVG(orders.total_amount) as avg_order, MAX(orders.created_at) as last_order')
                ->whereBetween('orders.created_at', [$from, $to])->where('orders.payment_status', 'paid')
                ->groupBy('users.id', 'users.name', 'users.email')->orderByDesc('total_spent'),
            fn($c) => [$c->name, $c->email, $c->orders_count, number_format($c->total_spent, 2), number_format($c->avg_order, 2), Carbon::parse($c->last_order)->format('Y-m-d')],
            true
        );

        $sections[] = $buildSection(
            'Sellers',
            ['Store', 'Rating', 'Orders', 'Gross Revenue', 'Commission', 'Net Earnings'],
            DB::table('seller_earnings')->join('sellers', 'sellers.id', '=', 'seller_earnings.seller_id')
                ->selectRaw('sellers.store_name, sellers.rating, COUNT(DISTINCT seller_earnings.order_id) as orders_count, SUM(seller_earnings.gross_amount) as revenue, SUM(seller_earnings.commission) as commission, SUM(seller_earnings.net_amount) as net')
                ->whereBetween('seller_earnings.created_at', [$from, $to])
                ->groupBy('sellers.id', 'sellers.store_name', 'sellers.rating')->orderByDesc('revenue'),
            fn($s) => [$s->store_name, $s->rating, $s->orders_count, number_format($s->revenue, 2), number_format($s->commission, 2), number_format($s->net, 2)],
            true
        );

        $sections[] = $buildSection(
            'Payments',
            ['Seller', 'Method', 'Amount', 'Status', 'Date'],
            DB::table('seller_withdraws')->join('sellers', 'sellers.id', '=', 'seller_withdraws.seller_id')
                ->selectRaw('sellers.store_name, seller_withdraws.method, seller_withdraws.amount, seller_withdraws.status, seller_withdraws.created_at')
                ->whereBetween('seller_withdraws.created_at', [$from, $to])->orderByDesc('seller_withdraws.created_at'),
            fn($w) => [$w->store_name, $w->method, number_format($w->amount, 2), $w->status, Carbon::parse($w->created_at)->format('Y-m-d')]
        );

        $sections[] = $buildSection(
            'Refunds',
            ['Order #', 'Customer', 'Seller', 'Amount', 'Reason', 'Status', 'Date'],
            DB::table('refund_requests')->join('orders', 'orders.id', '=', 'refund_requests.order_id')->join('users', 'users.id', '=', 'refund_requests.user_id')
                ->leftJoin('sellers', 'sellers.id', '=', 'refund_requests.seller_id')
                ->selectRaw('orders.order_number, users.name as customer, sellers.store_name as seller, refund_requests.amount, refund_requests.reason, refund_requests.status, refund_requests.created_at')
                ->whereBetween('refund_requests.created_at', [$from, $to])->orderByDesc('refund_requests.created_at'),
            fn($r) => [$r->order_number, $r->customer, $r->seller ?? '—', number_format($r->amount, 2), $r->reason, $r->status, Carbon::parse($r->created_at)->format('Y-m-d')]
        );

        $sections[] = $buildSection(
            'Support',
            ['Ticket #', 'Customer', 'Subject', 'Priority', 'Department', 'Status', 'Created', 'Resolved'],
            SupportTicket::whereBetween('created_at', [$from, $to])->with('user:id,name')->orderByDesc('created_at'),
            fn($t) => [$t->ticket_number, $t->user->name ?? '—', $t->subject, $t->priority, $t->department ?? 'general', $t->status, $t->created_at->format('Y-m-d'), $t->resolved_at ? $t->resolved_at->format('Y-m-d') : '—']
        );

        $pdf = Pdf::loadView('exports.report-full-pdf', [
            'sections'    => $sections,
            'rangeLabel'  => $rangeLabel,
            'appName'     => Setting::get('general', 'site_name', config('app.name')),
            'generatedAt' => now()->format('M d, Y h:i A'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('full_report_' . now()->format('Ymd_His') . '.pdf');
    }

    // ─── Async (queued) export ───────────────────────

    public function queueExport(Request $request, string $type)
    {
        $allowed = ['sales', 'revenue', 'products', 'customers', 'sellers', 'payments', 'refunds', 'support', 'full'];
        if (!in_array($type, $allowed)) {
            return response()->json(['error' => 'Invalid report type'], 422);
        }

        ProcessReportExport::dispatch(
            Auth::id(),
            $type,
            $request->input('from'),
            $request->input('to'),
        );

        return response()->json([
            'message' => 'Your export is being processed. You will be notified when it is ready for download.',
            'queued'  => true,
        ]);
    }

    public function downloadExport(string $filename)
    {
        $safe = basename($filename);
        $path = 'exports/' . $safe;

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Export file not found or has expired.');
        }

        return Storage::disk('local')->download($path, $safe, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
