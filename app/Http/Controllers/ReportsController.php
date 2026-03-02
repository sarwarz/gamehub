<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SellerEarning;
use App\Models\SellerOffer;
use App\Models\SellerWithdraw;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use App\Models\RefundRequest;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        return view('content.reports.index');
    }

    // ─── helpers ────────────────────────────────────────────

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

    private function previousRange(Carbon $from, Carbon $to): array
    {
        $diff = $from->diffInDays($to);
        return [
            $from->copy()->subDays($diff + 1)->startOfDay(),
            $from->copy()->subDay()->endOfDay(),
        ];
    }

    private function period(Request $request): string
    {
        $allowed = ['daily', 'weekly', 'monthly'];
        return in_array($request->period, $allowed) ? $request->period : 'daily';
    }

    private function groupFormat(string $period): string
    {
        return match ($period) {
            'weekly'  => '%x-W%v',
            'monthly' => '%Y-%m',
            default   => '%Y-%m-%d',
        };
    }

    private function labelFormat(string $period): string
    {
        return match ($period) {
            'weekly'  => 'W',
            'monthly' => 'M Y',
            default   => 'M d',
        };
    }

    private function buildTimeSeries(Carbon $from, Carbon $to, string $period, $rows, string $valueKey = 'value'): array
    {
        $labels = [];
        $values = [];

        $lookup = [];
        foreach ($rows as $r) {
            $lookup[$r->date_group] = (float) $r->$valueKey;
        }

        $interval = match ($period) {
            'weekly'  => '1 week',
            'monthly' => '1 month',
            default   => '1 day',
        };

        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $key = match ($period) {
                'weekly'  => $cursor->format('o') . '-W' . str_pad($cursor->isoWeek(), 2, '0', STR_PAD_LEFT),
                'monthly' => $cursor->format('Y-m'),
                default   => $cursor->format('Y-m-d'),
            };

            $labels[] = match ($period) {
                'weekly'  => 'W' . $cursor->isoWeek(),
                'monthly' => $cursor->format('M Y'),
                default   => $cursor->format('M d'),
            };

            $values[] = $lookup[$key] ?? 0;

            $cursor = match ($period) {
                'weekly'  => $cursor->addWeek(),
                'monthly' => $cursor->addMonth(),
                default   => $cursor->addDay(),
            };
        }

        return compact('labels', 'values');
    }

    private function pctChange(float $current, float $previous): float
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round(($current - $previous) / $previous * 100, 1);
    }

    // ─── Tab 1: Sales Overview ──────────────────────────────

    public function salesData(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        [$prevFrom, $prevTo] = $this->previousRange($from, $to);
        $period = $this->period($request);
        $fmt = $this->groupFormat($period);

        $totalOrders  = Order::whereBetween('created_at', [$from, $to])->count();
        $totalRevenue = (float) Order::whereBetween('created_at', [$from, $to])->where('payment_status', 'paid')->sum('total_amount');
        $avgOrder     = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;
        $totalUsers   = User::where('created_at', '<=', $to)->count();
        $convRate     = $totalUsers > 0 ? round(($totalOrders / $totalUsers) * 100, 1) : 0;

        $prevOrders  = Order::whereBetween('created_at', [$prevFrom, $prevTo])->count();
        $prevRevenue = (float) Order::whereBetween('created_at', [$prevFrom, $prevTo])->where('payment_status', 'paid')->sum('total_amount');
        $prevAvg     = $prevOrders > 0 ? round($prevRevenue / $prevOrders, 2) : 0;

        $orderSeries = DB::table('orders')
            ->selectRaw("DATE_FORMAT(created_at, '{$fmt}') as date_group, COUNT(*) as value")
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date_group')->orderBy('date_group')->get();

        $revenueSeries = DB::table('orders')
            ->selectRaw("DATE_FORMAT(created_at, '{$fmt}') as date_group, SUM(total_amount) as value")
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->groupBy('date_group')->orderBy('date_group')->get();

        $orderTs   = $this->buildTimeSeries($from, $to, $period, $orderSeries);
        $revenueTs = $this->buildTimeSeries($from, $to, $period, $revenueSeries);

        $statusBreakdown = Order::whereBetween('created_at', [$from, $to])
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')->pluck('total', 'status');

        $paymentDist = Order::whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->selectRaw("COALESCE(payment_method, 'unknown') as gateway, COUNT(*) as total")
            ->groupBy('gateway')->pluck('total', 'gateway');

        $topOrders = Order::whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->with('user:id,name,email')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get(['id', 'user_id', 'order_number', 'total_amount', 'status', 'created_at']);

        return response()->json([
            'kpi' => [
                ['label' => 'Total Orders',    'value' => $totalOrders,  'change' => $this->pctChange($totalOrders, $prevOrders),   'icon' => 'tabler-shopping-cart', 'color' => 'primary'],
                ['label' => 'Total Revenue',    'value' => $totalRevenue, 'change' => $this->pctChange($totalRevenue, $prevRevenue), 'icon' => 'tabler-currency-dollar', 'color' => 'success', 'format' => 'currency'],
                ['label' => 'Avg Order Value',  'value' => $avgOrder,     'change' => $this->pctChange($avgOrder, $prevAvg),         'icon' => 'tabler-receipt', 'color' => 'info',    'format' => 'currency'],
                ['label' => 'Conversion Rate',  'value' => $convRate,     'change' => 0,                                             'icon' => 'tabler-percentage', 'color' => 'warning', 'format' => 'percent'],
            ],
            'line_chart' => [
                'labels'   => $orderTs['labels'],
                'orders'   => $orderTs['values'],
                'revenue'  => $revenueTs['values'],
            ],
            'bar_chart' => [
                'labels' => array_keys($statusBreakdown->toArray()),
                'values' => array_values($statusBreakdown->toArray()),
            ],
            'donut_chart' => [
                'labels' => array_keys($paymentDist->toArray()),
                'values' => array_values($paymentDist->toArray()),
            ],
            'table' => $topOrders->map(fn($o) => [
                'order_number' => $o->order_number,
                'customer'     => $o->user->name ?? '—',
                'amount'       => number_format($o->total_amount, 2),
                'status'       => $o->status,
                'date'         => $o->created_at->format('M d, Y'),
            ]),
        ]);
    }

    // ─── Tab 2: Revenue & Commission ────────────────────────

    public function revenueData(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        [$prevFrom, $prevTo] = $this->previousRange($from, $to);
        $period = $this->period($request);
        $fmt = $this->groupFormat($period);

        $gross      = (float) Order::whereBetween('created_at', [$from, $to])->where('payment_status', 'paid')->sum('total_amount');
        $commission = (float) SellerEarning::whereBetween('created_at', [$from, $to])->sum('commission');
        $sellerNet  = (float) SellerEarning::whereBetween('created_at', [$from, $to])->sum('net_amount');
        $netProfit  = $gross - $sellerNet;

        $prevGross     = (float) Order::whereBetween('created_at', [$prevFrom, $prevTo])->where('payment_status', 'paid')->sum('total_amount');
        $prevCommission = (float) SellerEarning::whereBetween('created_at', [$prevFrom, $prevTo])->sum('commission');
        $prevSellerNet = (float) SellerEarning::whereBetween('created_at', [$prevFrom, $prevTo])->sum('net_amount');
        $prevNetProfit = $prevGross - $prevSellerNet;

        $commissionVsSeller = DB::table('seller_earnings')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(commission) as commission, SUM(net_amount) as seller_net")
            ->whereBetween('created_at', [now()->subMonths(11)->startOfMonth(), $to])
            ->groupBy('month')->orderBy('month')->get();

        $revenueTrend = DB::table('orders')
            ->selectRaw("DATE_FORMAT(created_at, '{$fmt}') as date_group, SUM(total_amount) as revenue, SUM(tax_amount) as tax, SUM(discount_amount) as discount")
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->groupBy('date_group')->orderBy('date_group')->get();

        $gatewayRevenue = Order::whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->selectRaw("COALESCE(payment_method, 'unknown') as gateway, SUM(total_amount) as total")
            ->groupBy('gateway')->pluck('total', 'gateway');

        $sellerBreakdown = DB::table('seller_earnings')
            ->join('sellers', 'sellers.id', '=', 'seller_earnings.seller_id')
            ->selectRaw("sellers.store_name, SUM(seller_earnings.gross_amount) as gross, SUM(seller_earnings.commission) as commission, SUM(seller_earnings.net_amount) as net, COUNT(DISTINCT seller_earnings.order_id) as orders_count")
            ->whereBetween('seller_earnings.created_at', [$from, $to])
            ->groupBy('sellers.id', 'sellers.store_name')
            ->orderByDesc('gross')
            ->limit(20)
            ->get();

        return response()->json([
            'kpi' => [
                ['label' => 'Gross Revenue',      'value' => $gross,      'change' => $this->pctChange($gross, $prevGross),           'icon' => 'tabler-report-money', 'color' => 'primary', 'format' => 'currency'],
                ['label' => 'Platform Commission', 'value' => $commission, 'change' => $this->pctChange($commission, $prevCommission), 'icon' => 'tabler-coin',         'color' => 'success', 'format' => 'currency'],
                ['label' => 'Seller Payouts',      'value' => $sellerNet, 'change' => $this->pctChange($sellerNet, $prevSellerNet),   'icon' => 'tabler-cash',         'color' => 'warning', 'format' => 'currency'],
                ['label' => 'Net Profit',          'value' => $netProfit, 'change' => $this->pctChange($netProfit, $prevNetProfit),   'icon' => 'tabler-trophy',       'color' => 'info',    'format' => 'currency'],
            ],
            'stacked_bar' => [
                'months'     => $commissionVsSeller->pluck('month')->map(fn($m) => Carbon::parse($m . '-01')->format('M Y')),
                'commission' => $commissionVsSeller->pluck('commission')->map(fn($v) => (float) $v),
                'seller_net' => $commissionVsSeller->pluck('seller_net')->map(fn($v) => (float) $v),
            ],
            'area_chart' => [
                'labels'   => $revenueTrend->pluck('date_group'),
                'revenue'  => $revenueTrend->pluck('revenue')->map(fn($v) => (float) $v),
                'tax'      => $revenueTrend->pluck('tax')->map(fn($v) => (float) $v),
                'discount' => $revenueTrend->pluck('discount')->map(fn($v) => (float) $v),
            ],
            'donut_chart' => [
                'labels' => array_keys($gatewayRevenue->toArray()),
                'values' => array_values($gatewayRevenue->toArray()),
            ],
            'table' => $sellerBreakdown->map(fn($s) => [
                'store_name'  => $s->store_name,
                'gross'       => number_format($s->gross, 2),
                'commission'  => number_format($s->commission, 2),
                'net'         => number_format($s->net, 2),
                'orders_count' => $s->orders_count,
            ]),
        ]);
    }

    // ─── Tab 3: Product Analytics ───────────────────────────

    public function productData(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        [$prevFrom, $prevTo] = $this->previousRange($from, $to);

        $totalProducts = Product::count();
        $activeOffers  = SellerOffer::where('status', 'active')->count();
        $unitsSold     = (int) OrderItem::whereHas('order', fn($q) => $q->whereBetween('created_at', [$from, $to])->where('payment_status', 'paid'))->sum('quantity');
        $avgRating     = round((float) DB::table('product_reviews')->avg('rating'), 1);

        $prevUnitsSold = (int) OrderItem::whereHas('order', fn($q) => $q->whereBetween('created_at', [$prevFrom, $prevTo])->where('payment_status', 'paid'))->sum('quantity');

        $topByUnits = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('products.title, SUM(order_items.quantity) as units')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('units')
            ->limit(10)->get();

        $topByRevenue = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('products.title, SUM(order_items.subtotal) as revenue')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('revenue')
            ->limit(10)->get();

        $categoryDist = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('category_product', 'category_product.product_id', '=', 'products.id')
            ->join('product_categories', 'product_categories.id', '=', 'category_product.category_id')
            ->selectRaw('product_categories.name as category, SUM(order_items.subtotal) as revenue')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->groupBy('product_categories.id', 'product_categories.name')
            ->orderByDesc('revenue')
            ->limit(8)->get();

        $productTable = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('products.title, SUM(order_items.quantity) as units, SUM(order_items.subtotal) as revenue, AVG(order_items.unit_price) as avg_price')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('revenue')
            ->limit(20)->get();

        return response()->json([
            'kpi' => [
                ['label' => 'Total Products',  'value' => $totalProducts, 'change' => 0,                                              'icon' => 'tabler-package',    'color' => 'primary'],
                ['label' => 'Active Offers',    'value' => $activeOffers,  'change' => 0,                                              'icon' => 'tabler-tag',        'color' => 'success'],
                ['label' => 'Units Sold',       'value' => $unitsSold,     'change' => $this->pctChange($unitsSold, $prevUnitsSold),   'icon' => 'tabler-truck',      'color' => 'info'],
                ['label' => 'Avg Rating',       'value' => $avgRating,     'change' => 0,                                              'icon' => 'tabler-star',       'color' => 'warning', 'format' => 'rating'],
            ],
            'horizontal_bar' => [
                'labels' => $topByUnits->pluck('title'),
                'values' => $topByUnits->pluck('units')->map(fn($v) => (int) $v),
            ],
            'bar_chart' => [
                'labels' => $topByRevenue->pluck('title'),
                'values' => $topByRevenue->pluck('revenue')->map(fn($v) => (float) $v),
            ],
            'donut_chart' => [
                'labels' => $categoryDist->pluck('category'),
                'values' => $categoryDist->pluck('revenue')->map(fn($v) => (float) $v),
            ],
            'table' => $productTable->map(fn($p) => [
                'product'   => $p->title,
                'units'     => (int) $p->units,
                'revenue'   => number_format($p->revenue, 2),
                'avg_price' => number_format($p->avg_price, 2),
            ]),
        ]);
    }

    // ─── Tab 4: Customer Insights ───────────────────────────

    public function customerData(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        [$prevFrom, $prevTo] = $this->previousRange($from, $to);
        $period = $this->period($request);
        $fmt = $this->groupFormat($period);

        $totalCustomers = User::whereDoesntHave('roles')->count();
        $newCustomers   = User::whereDoesntHave('roles')->whereBetween('created_at', [$from, $to])->count();
        $prevNewCust    = User::whereDoesntHave('roles')->whereBetween('created_at', [$prevFrom, $prevTo])->count();

        $returningBuyers = (int) DB::table('orders')
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->selectRaw('COUNT(DISTINCT user_id) as cnt')
            ->whereIn('user_id', function ($q) use ($from) {
                $q->select('user_id')->from('orders')
                  ->where('created_at', '<', $from)
                  ->where('payment_status', 'paid');
            })->value('cnt');

        $avgLtv = (float) DB::query()
            ->selectRaw('AVG(user_total) as avg_ltv')
            ->fromSub(
                DB::table('orders')->where('payment_status', 'paid')
                    ->selectRaw('user_id, SUM(total_amount) as user_total')
                    ->groupBy('user_id'),
                'sub'
            )->value('avg_ltv');

        $regSeries = DB::table('users')
            ->selectRaw("DATE_FORMAT(created_at, '{$fmt}') as date_group, COUNT(*) as value")
            ->whereBetween('created_at', [$from, $to])
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('role_user')->whereColumn('role_user.user_id', 'users.id');
            })
            ->groupBy('date_group')->orderBy('date_group')->get();

        $regTs = $this->buildTimeSeries($from, $to, $period, $regSeries);

        $topCustomers = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.name, users.email, SUM(orders.total_amount) as total_spent, COUNT(orders.id) as orders_count')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent')
            ->limit(10)->get();

        $orderFrequency = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.payment_status', 'paid')
            ->selectRaw('users.id, COUNT(orders.id) as order_count')
            ->groupBy('users.id')
            ->get()
            ->groupBy(function ($row) {
                $c = $row->order_count;
                if ($c == 1) return '1 order';
                if ($c <= 3) return '2-3 orders';
                if ($c <= 5) return '4-5 orders';
                if ($c <= 10) return '6-10 orders';
                return '10+ orders';
            })
            ->map(fn($group) => $group->count());

        $customerTable = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.name, COUNT(orders.id) as orders_count, SUM(orders.total_amount) as total_spent, AVG(orders.total_amount) as avg_order, MAX(orders.created_at) as last_order')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_spent')
            ->limit(20)->get();

        return response()->json([
            'kpi' => [
                ['label' => 'Total Customers',  'value' => $totalCustomers, 'change' => 0,                                             'icon' => 'tabler-users',     'color' => 'primary'],
                ['label' => 'New This Period',   'value' => $newCustomers,   'change' => $this->pctChange($newCustomers, $prevNewCust), 'icon' => 'tabler-user-plus', 'color' => 'success'],
                ['label' => 'Returning Buyers',  'value' => $returningBuyers,'change' => 0,                                             'icon' => 'tabler-refresh',   'color' => 'info'],
                ['label' => 'Avg Lifetime Value','value' => round($avgLtv, 2),'change' => 0,                                            'icon' => 'tabler-diamond',   'color' => 'warning', 'format' => 'currency'],
            ],
            'area_chart' => [
                'labels' => $regTs['labels'],
                'values' => $regTs['values'],
            ],
            'bar_chart' => [
                'labels' => $topCustomers->pluck('name'),
                'values' => $topCustomers->pluck('total_spent')->map(fn($v) => (float) $v),
            ],
            'donut_chart' => [
                'labels' => array_keys($orderFrequency->toArray()),
                'values' => array_values($orderFrequency->toArray()),
            ],
            'table' => $customerTable->map(fn($c) => [
                'name'        => $c->name,
                'orders'      => $c->orders_count,
                'total_spent' => number_format($c->total_spent, 2),
                'avg_order'   => number_format($c->avg_order, 2),
                'last_order'  => Carbon::parse($c->last_order)->format('M d, Y'),
            ]),
        ]);
    }

    // ─── Tab 5: Seller Performance ──────────────────────────

    public function sellerData(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        [$prevFrom, $prevTo] = $this->previousRange($from, $to);
        $period = $this->period($request);
        $fmt = $this->groupFormat($period);

        $activeSellers    = Seller::where('status', 'active')->count();
        $totalSellerRev   = (float) SellerEarning::whereBetween('created_at', [$from, $to])->sum('gross_amount');
        $pendingPayouts   = (float) SellerWithdraw::where('status', 'pending')->sum('amount');
        $avgRating        = round((float) Seller::where('status', 'active')->avg('rating'), 1);

        $prevSellerRev = (float) SellerEarning::whereBetween('created_at', [$prevFrom, $prevTo])->sum('gross_amount');

        $topSellers = DB::table('seller_earnings')
            ->join('sellers', 'sellers.id', '=', 'seller_earnings.seller_id')
            ->selectRaw('sellers.store_name, SUM(seller_earnings.gross_amount) as revenue')
            ->whereBetween('seller_earnings.created_at', [$from, $to])
            ->groupBy('sellers.id', 'sellers.store_name')
            ->orderByDesc('revenue')
            ->limit(10)->get();

        $earningsStatus = DB::table('seller_earnings')
            ->selectRaw("status, SUM(net_amount) as total")
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('status')
            ->pluck('total', 'status');

        $appSeries = DB::table('sellers')
            ->selectRaw("DATE_FORMAT(created_at, '{$fmt}') as date_group, COUNT(*) as value")
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date_group')->orderBy('date_group')->get();

        $appTs = $this->buildTimeSeries($from, $to, $period, $appSeries);

        $sellerTable = DB::table('seller_earnings')
            ->join('sellers', 'sellers.id', '=', 'seller_earnings.seller_id')
            ->selectRaw('sellers.store_name, sellers.rating, COUNT(DISTINCT seller_earnings.order_id) as orders_count, SUM(seller_earnings.gross_amount) as revenue, SUM(seller_earnings.commission) as commission')
            ->whereBetween('seller_earnings.created_at', [$from, $to])
            ->groupBy('sellers.id', 'sellers.store_name', 'sellers.rating')
            ->orderByDesc('revenue')
            ->limit(20)->get();

        return response()->json([
            'kpi' => [
                ['label' => 'Active Sellers',     'value' => $activeSellers,  'change' => 0,                                                 'icon' => 'tabler-building-store', 'color' => 'primary'],
                ['label' => 'Total Seller Revenue','value' => $totalSellerRev, 'change' => $this->pctChange($totalSellerRev, $prevSellerRev), 'icon' => 'tabler-report-money',   'color' => 'success', 'format' => 'currency'],
                ['label' => 'Pending Payouts',     'value' => $pendingPayouts, 'change' => 0,                                                 'icon' => 'tabler-clock-dollar',   'color' => 'warning', 'format' => 'currency'],
                ['label' => 'Avg Rating',          'value' => $avgRating,      'change' => 0,                                                 'icon' => 'tabler-star',           'color' => 'info',    'format' => 'rating'],
            ],
            'bar_chart' => [
                'labels' => $topSellers->pluck('store_name'),
                'values' => $topSellers->pluck('revenue')->map(fn($v) => (float) $v),
            ],
            'stacked_bar' => [
                'labels' => array_keys($earningsStatus->toArray()),
                'values' => array_values($earningsStatus->toArray()),
            ],
            'line_chart' => [
                'labels' => $appTs['labels'],
                'values' => $appTs['values'],
            ],
            'table' => $sellerTable->map(fn($s) => [
                'store_name' => $s->store_name,
                'orders'     => $s->orders_count,
                'revenue'    => number_format($s->revenue, 2),
                'commission' => number_format($s->commission, 2),
                'rating'     => $s->rating,
            ]),
        ]);
    }

    // ─── Tab 6: Payment & Wallet ────────────────────────────

    public function paymentData(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        [$prevFrom, $prevTo] = $this->previousRange($from, $to);
        $period = $this->period($request);
        $fmt = $this->groupFormat($period);

        $totalTx     = WalletTransaction::whereBetween('created_at', [$from, $to])->count();
        $deposits    = (float) WalletTransaction::whereBetween('created_at', [$from, $to])->where('source', 'deposit')->sum('amount');
        $walletPay   = (float) WalletTransaction::whereBetween('created_at', [$from, $to])->where('source', 'order')->sum('amount');
        $withdrawals = (float) SellerWithdraw::whereBetween('created_at', [$from, $to])->where('status', 'approved')->sum('amount');

        $prevTotalTx = WalletTransaction::whereBetween('created_at', [$prevFrom, $prevTo])->count();

        $creditSeries = DB::table('wallet_transactions')
            ->selectRaw("DATE_FORMAT(created_at, '{$fmt}') as date_group, SUM(amount) as value")
            ->whereBetween('created_at', [$from, $to])
            ->where('type', 'credit')
            ->groupBy('date_group')->orderBy('date_group')->get();

        $debitSeries = DB::table('wallet_transactions')
            ->selectRaw("DATE_FORMAT(created_at, '{$fmt}') as date_group, SUM(amount) as value")
            ->whereBetween('created_at', [$from, $to])
            ->where('type', 'debit')
            ->groupBy('date_group')->orderBy('date_group')->get();

        $creditTs = $this->buildTimeSeries($from, $to, $period, $creditSeries);
        $debitTs  = $this->buildTimeSeries($from, $to, $period, $debitSeries);

        $txByCategory = WalletTransaction::whereBetween('created_at', [$from, $to])
            ->selectRaw("COALESCE(source, 'other') as category, COUNT(*) as total")
            ->groupBy('category')->pluck('total', 'category');

        $gatewayShare = Order::whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->selectRaw("COALESCE(payment_method, 'unknown') as gateway, COUNT(*) as total")
            ->groupBy('gateway')->pluck('total', 'gateway');

        $withdrawTable = DB::table('seller_withdraws')
            ->join('sellers', 'sellers.id', '=', 'seller_withdraws.seller_id')
            ->selectRaw('sellers.store_name, seller_withdraws.method, seller_withdraws.amount, seller_withdraws.status, seller_withdraws.created_at')
            ->whereBetween('seller_withdraws.created_at', [$from, $to])
            ->orderByDesc('seller_withdraws.created_at')
            ->limit(20)->get();

        return response()->json([
            'kpi' => [
                ['label' => 'Total Transactions', 'value' => $totalTx,    'change' => $this->pctChange($totalTx, $prevTotalTx), 'icon' => 'tabler-arrows-exchange', 'color' => 'primary'],
                ['label' => 'Wallet Deposits',     'value' => $deposits,   'change' => 0, 'icon' => 'tabler-wallet',  'color' => 'success', 'format' => 'currency'],
                ['label' => 'Wallet Payments',     'value' => $walletPay,  'change' => 0, 'icon' => 'tabler-cash',    'color' => 'info',    'format' => 'currency'],
                ['label' => 'Seller Withdrawals',  'value' => $withdrawals,'change' => 0, 'icon' => 'tabler-transfer','color' => 'warning', 'format' => 'currency'],
            ],
            'area_chart' => [
                'labels'  => $creditTs['labels'],
                'credits' => $creditTs['values'],
                'debits'  => $debitTs['values'],
            ],
            'bar_chart' => [
                'labels' => array_keys($txByCategory->toArray()),
                'values' => array_values($txByCategory->toArray()),
            ],
            'donut_chart' => [
                'labels' => array_keys($gatewayShare->toArray()),
                'values' => array_values($gatewayShare->toArray()),
            ],
            'table' => $withdrawTable->map(fn($w) => [
                'store_name' => $w->store_name,
                'method'     => $w->method,
                'amount'     => number_format($w->amount, 2),
                'status'     => $w->status,
                'date'       => Carbon::parse($w->created_at)->format('M d, Y'),
            ]),
        ]);
    }

    // ─── Tab 7: Refunds & Disputes ──────────────────────────

    public function refundData(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        [$prevFrom, $prevTo] = $this->previousRange($from, $to);
        $period = $this->period($request);
        $fmt = $this->groupFormat($period);

        $totalRefunds   = RefundRequest::whereBetween('created_at', [$from, $to])->count();
        $approvedAmt    = (float) RefundRequest::whereBetween('created_at', [$from, $to])->where('status', 'approved')->sum('amount');
        $pendingCount   = RefundRequest::whereBetween('created_at', [$from, $to])->where('status', 'pending')->count();
        $totalOrders    = Order::whereBetween('created_at', [$from, $to])->count();
        $refundRate     = $totalOrders > 0 ? round(($totalRefunds / $totalOrders) * 100, 1) : 0;

        $prevRefunds = RefundRequest::whereBetween('created_at', [$prevFrom, $prevTo])->count();

        $refundSeries = DB::table('refund_requests')
            ->selectRaw("DATE_FORMAT(created_at, '{$fmt}') as date_group, COUNT(*) as value")
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date_group')->orderBy('date_group')->get();

        $refundTs = $this->buildTimeSeries($from, $to, $period, $refundSeries);

        $statusDist = RefundRequest::whereBetween('created_at', [$from, $to])
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')->pluck('total', 'status');

        $refundBySeller = DB::table('refund_requests')
            ->join('sellers', 'sellers.id', '=', 'refund_requests.seller_id')
            ->selectRaw('sellers.store_name, COUNT(*) as total')
            ->whereBetween('refund_requests.created_at', [$from, $to])
            ->groupBy('sellers.id', 'sellers.store_name')
            ->orderByDesc('total')
            ->limit(10)->get();

        $refundTable = DB::table('refund_requests')
            ->join('orders', 'orders.id', '=', 'refund_requests.order_id')
            ->join('users', 'users.id', '=', 'refund_requests.user_id')
            ->leftJoin('sellers', 'sellers.id', '=', 'refund_requests.seller_id')
            ->selectRaw('orders.order_number, users.name as customer, sellers.store_name as seller, refund_requests.amount, refund_requests.reason, refund_requests.status, refund_requests.created_at')
            ->whereBetween('refund_requests.created_at', [$from, $to])
            ->orderByDesc('refund_requests.created_at')
            ->limit(20)->get();

        return response()->json([
            'kpi' => [
                ['label' => 'Total Refunds',  'value' => $totalRefunds, 'change' => $this->pctChange($totalRefunds, $prevRefunds), 'icon' => 'tabler-receipt-refund', 'color' => 'primary'],
                ['label' => 'Approved Amount', 'value' => $approvedAmt,  'change' => 0, 'icon' => 'tabler-check',       'color' => 'success', 'format' => 'currency'],
                ['label' => 'Pending Count',   'value' => $pendingCount, 'change' => 0, 'icon' => 'tabler-clock',       'color' => 'warning'],
                ['label' => 'Refund Rate',     'value' => $refundRate,   'change' => 0, 'icon' => 'tabler-percentage',  'color' => 'danger',  'format' => 'percent'],
            ],
            'line_chart' => [
                'labels' => $refundTs['labels'],
                'values' => $refundTs['values'],
            ],
            'donut_chart' => [
                'labels' => array_keys($statusDist->toArray()),
                'values' => array_values($statusDist->toArray()),
            ],
            'bar_chart' => [
                'labels' => $refundBySeller->pluck('store_name'),
                'values' => $refundBySeller->pluck('total')->map(fn($v) => (int) $v),
            ],
            'table' => $refundTable->map(fn($r) => [
                'order_number' => $r->order_number,
                'customer'     => $r->customer,
                'seller'       => $r->seller ?? '—',
                'amount'       => number_format($r->amount, 2),
                'reason'       => $r->reason,
                'status'       => $r->status,
            ]),
        ]);
    }

    // ─── Tab 8: Support Tickets ─────────────────────────────

    public function supportData(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        [$prevFrom, $prevTo] = $this->previousRange($from, $to);
        $period = $this->period($request);
        $fmt = $this->groupFormat($period);

        $totalTickets = SupportTicket::whereBetween('created_at', [$from, $to])->count();
        $openTickets  = SupportTicket::whereBetween('created_at', [$from, $to])->whereNotIn('status', ['resolved', 'closed'])->count();

        $avgResolution = SupportTicket::whereBetween('created_at', [$from, $to])
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours');
        $avgResolution = $avgResolution ? round($avgResolution, 1) : 0;

        $escalatedCount = SupportTicket::whereBetween('created_at', [$from, $to])->where('is_escalated', true)->count();
        $escalationRate = $totalTickets > 0 ? round(($escalatedCount / $totalTickets) * 100, 1) : 0;

        $prevTickets = SupportTicket::whereBetween('created_at', [$prevFrom, $prevTo])->count();

        $openedSeries = DB::table('support_tickets')
            ->selectRaw("DATE_FORMAT(created_at, '{$fmt}') as date_group, COUNT(*) as value")
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date_group')->orderBy('date_group')->get();

        $resolvedSeries = DB::table('support_tickets')
            ->selectRaw("DATE_FORMAT(resolved_at, '{$fmt}') as date_group, COUNT(*) as value")
            ->whereBetween('resolved_at', [$from, $to])
            ->whereNotNull('resolved_at')
            ->groupBy('date_group')->orderBy('date_group')->get();

        $openedTs   = $this->buildTimeSeries($from, $to, $period, $openedSeries);
        $resolvedTs = $this->buildTimeSeries($from, $to, $period, $resolvedSeries);

        $deptDist = SupportTicket::whereBetween('created_at', [$from, $to])
            ->selectRaw("COALESCE(department, 'general') as dept, COUNT(*) as total")
            ->groupBy('dept')->orderByDesc('total')->pluck('total', 'dept');

        $priorityDist = SupportTicket::whereBetween('created_at', [$from, $to])
            ->selectRaw("priority, COUNT(*) as total")
            ->groupBy('priority')->pluck('total', 'priority');

        $unresolvedTable = SupportTicket::whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', ['resolved', 'closed'])
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['id', 'ticket_number', 'user_id', 'subject', 'priority', 'department', 'created_at', 'last_reply_at']);

        return response()->json([
            'kpi' => [
                ['label' => 'Total Tickets',    'value' => $totalTickets,  'change' => $this->pctChange($totalTickets, $prevTickets), 'icon' => 'tabler-ticket',       'color' => 'primary'],
                ['label' => 'Open Tickets',      'value' => $openTickets,   'change' => 0, 'icon' => 'tabler-folder-open', 'color' => 'warning'],
                ['label' => 'Avg Resolution (h)','value' => $avgResolution, 'change' => 0, 'icon' => 'tabler-clock',       'color' => 'success'],
                ['label' => 'Escalation Rate',   'value' => $escalationRate,'change' => 0, 'icon' => 'tabler-alert-triangle','color' => 'danger', 'format' => 'percent'],
            ],
            'area_chart' => [
                'labels'   => $openedTs['labels'],
                'opened'   => $openedTs['values'],
                'resolved' => $resolvedTs['values'],
            ],
            'bar_chart' => [
                'labels' => array_keys($deptDist->toArray()),
                'values' => array_values($deptDist->toArray()),
            ],
            'donut_chart' => [
                'labels' => array_keys($priorityDist->toArray()),
                'values' => array_values($priorityDist->toArray()),
            ],
            'table' => $unresolvedTable->map(fn($t) => [
                'ticket_number' => $t->ticket_number,
                'subject'       => $t->subject,
                'priority'      => $t->priority,
                'department'    => $t->department ?? 'general',
                'created'       => $t->created_at->format('M d, Y'),
                'last_reply'    => $t->last_reply_at ? $t->last_reply_at->format('M d, Y') : '—',
            ]),
        ]);
    }
}
