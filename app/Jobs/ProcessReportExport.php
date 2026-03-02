<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\ReportExportReady;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessReportExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 2;

    private const LAZY_CHUNK = 1000;

    public function __construct(
        private int     $userId,
        private string  $reportType,
        private ?string $from,
        private ?string $to,
    ) {}

    public function handle(): void
    {
        $from = $this->from ? Carbon::parse($this->from)->startOfDay() : now()->subDays(29)->startOfDay();
        $to   = $this->to   ? Carbon::parse($this->to)->endOfDay()     : now()->endOfDay();

        $filename = "{$this->reportType}_report_" . now()->format('Ymd_His') . "_{$this->userId}.csv";
        $dir = storage_path('app/exports');

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir . '/' . $filename;
        $out  = fopen($path, 'w');

        try {
            if ($this->reportType === 'full') {
                $this->writeFull($out, $from, $to);
            } else {
                $method = 'write' . ucfirst($this->reportType);
                $this->$method($out, $from, $to);
            }
        } catch (\Throwable $e) {
            fclose($out);
            @unlink($path);
            Log::error("Report export failed [{$this->reportType}]: " . $e->getMessage());
            throw $e;
        }

        fclose($out);

        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new ReportExportReady($filename, $this->reportType));
        }
    }

    // ─── Section writers ─────────────────────────────

    private function writeSales($out, Carbon $from, Carbon $to): void
    {
        fputcsv($out, ['Order #', 'Customer', 'Email', 'Amount', 'Status', 'Payment', 'Gateway', 'Date']);

        foreach (Order::whereBetween('created_at', [$from, $to])
            ->with('user:id,name,email')
            ->orderByDesc('created_at')
            ->lazy(self::LAZY_CHUNK) as $o) {
            fputcsv($out, [
                $o->order_number, $o->user->name ?? '—', $o->user->email ?? '—',
                number_format($o->total_amount, 2), $o->status, $o->payment_status,
                $o->payment_method ?? '—', $o->created_at->format('Y-m-d H:i'),
            ]);
        }
    }

    private function writeRevenue($out, Carbon $from, Carbon $to): void
    {
        fputcsv($out, ['Seller', 'Order #', 'Gross', 'Commission', 'Net', 'Status', 'Date']);

        foreach (DB::table('seller_earnings')
            ->join('sellers', 'sellers.id', '=', 'seller_earnings.seller_id')
            ->join('orders', 'orders.id', '=', 'seller_earnings.order_id')
            ->selectRaw('sellers.store_name, orders.order_number, seller_earnings.gross_amount, seller_earnings.commission, seller_earnings.net_amount, seller_earnings.status, seller_earnings.created_at')
            ->whereBetween('seller_earnings.created_at', [$from, $to])
            ->orderByDesc('seller_earnings.created_at')
            ->cursor() as $e) {
            fputcsv($out, [
                $e->store_name, $e->order_number,
                number_format($e->gross_amount, 2), number_format($e->commission, 2),
                number_format($e->net_amount, 2), $e->status,
                Carbon::parse($e->created_at)->format('Y-m-d'),
            ]);
        }
    }

    private function writeProducts($out, Carbon $from, Carbon $to): void
    {
        fputcsv($out, ['Product', 'Units Sold', 'Revenue', 'Avg Price']);

        foreach (DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('products.title, SUM(order_items.quantity) as units, SUM(order_items.subtotal) as revenue, AVG(order_items.unit_price) as avg_price')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('revenue')
            ->cursor() as $p) {
            fputcsv($out, [
                $p->title, (int) $p->units,
                number_format($p->revenue, 2), number_format($p->avg_price, 2),
            ]);
        }
    }

    private function writeCustomers($out, Carbon $from, Carbon $to): void
    {
        fputcsv($out, ['Name', 'Email', 'Orders', 'Total Spent', 'Avg Order', 'Last Order']);

        foreach (DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.name, users.email, COUNT(orders.id) as orders_count, SUM(orders.total_amount) as total_spent, AVG(orders.total_amount) as avg_order, MAX(orders.created_at) as last_order')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent')
            ->cursor() as $c) {
            fputcsv($out, [
                $c->name, $c->email, $c->orders_count,
                number_format($c->total_spent, 2), number_format($c->avg_order, 2),
                Carbon::parse($c->last_order)->format('Y-m-d'),
            ]);
        }
    }

    private function writeSellers($out, Carbon $from, Carbon $to): void
    {
        fputcsv($out, ['Store', 'Rating', 'Orders', 'Gross Revenue', 'Commission', 'Net Earnings']);

        foreach (DB::table('seller_earnings')
            ->join('sellers', 'sellers.id', '=', 'seller_earnings.seller_id')
            ->selectRaw('sellers.store_name, sellers.rating, COUNT(DISTINCT seller_earnings.order_id) as orders_count, SUM(seller_earnings.gross_amount) as revenue, SUM(seller_earnings.commission) as commission, SUM(seller_earnings.net_amount) as net')
            ->whereBetween('seller_earnings.created_at', [$from, $to])
            ->groupBy('sellers.id', 'sellers.store_name', 'sellers.rating')
            ->orderByDesc('revenue')
            ->cursor() as $s) {
            fputcsv($out, [
                $s->store_name, $s->rating, $s->orders_count,
                number_format($s->revenue, 2), number_format($s->commission, 2),
                number_format($s->net, 2),
            ]);
        }
    }

    private function writePayments($out, Carbon $from, Carbon $to): void
    {
        fputcsv($out, ['Seller', 'Method', 'Amount', 'Status', 'Date']);

        foreach (DB::table('seller_withdraws')
            ->join('sellers', 'sellers.id', '=', 'seller_withdraws.seller_id')
            ->selectRaw('sellers.store_name, seller_withdraws.method, seller_withdraws.amount, seller_withdraws.status, seller_withdraws.created_at')
            ->whereBetween('seller_withdraws.created_at', [$from, $to])
            ->orderByDesc('seller_withdraws.created_at')
            ->cursor() as $w) {
            fputcsv($out, [
                $w->store_name, $w->method,
                number_format($w->amount, 2), $w->status,
                Carbon::parse($w->created_at)->format('Y-m-d'),
            ]);
        }
    }

    private function writeRefunds($out, Carbon $from, Carbon $to): void
    {
        fputcsv($out, ['Order #', 'Customer', 'Seller', 'Amount', 'Reason', 'Status', 'Date']);

        foreach (DB::table('refund_requests')
            ->join('orders', 'orders.id', '=', 'refund_requests.order_id')
            ->join('users', 'users.id', '=', 'refund_requests.user_id')
            ->leftJoin('sellers', 'sellers.id', '=', 'refund_requests.seller_id')
            ->selectRaw('orders.order_number, users.name as customer, sellers.store_name as seller, refund_requests.amount, refund_requests.reason, refund_requests.status, refund_requests.created_at')
            ->whereBetween('refund_requests.created_at', [$from, $to])
            ->orderByDesc('refund_requests.created_at')
            ->cursor() as $r) {
            fputcsv($out, [
                $r->order_number, $r->customer, $r->seller ?? '—',
                number_format($r->amount, 2), $r->reason, $r->status,
                Carbon::parse($r->created_at)->format('Y-m-d'),
            ]);
        }
    }

    private function writeSupport($out, Carbon $from, Carbon $to): void
    {
        fputcsv($out, ['Ticket #', 'Customer', 'Subject', 'Priority', 'Department', 'Status', 'Created', 'Resolved']);

        foreach (SupportTicket::whereBetween('created_at', [$from, $to])
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->lazy(self::LAZY_CHUNK) as $t) {
            fputcsv($out, [
                $t->ticket_number, $t->user->name ?? '—', $t->subject,
                $t->priority, $t->department ?? 'general', $t->status,
                $t->created_at->format('Y-m-d'),
                $t->resolved_at ? $t->resolved_at->format('Y-m-d') : '—',
            ]);
        }
    }

    private function writeFull($out, Carbon $from, Carbon $to): void
    {
        $writers = [
            'SALES'     => 'writeSales',
            'REVENUE'   => 'writeRevenue',
            'PRODUCTS'  => 'writeProducts',
            'CUSTOMERS' => 'writeCustomers',
            'SELLERS'   => 'writeSellers',
            'PAYMENTS'  => 'writePayments',
            'REFUNDS'   => 'writeRefunds',
            'SUPPORT'   => 'writeSupport',
        ];

        foreach ($writers as $label => $method) {
            fputcsv($out, []);
            fputcsv($out, ["=== {$label} ==="]);
            $this->$method($out, $from, $to);
        }
    }
}
