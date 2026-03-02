<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\SellerEarning;
use App\Models\SellerOffer;
use App\Models\OrderItem;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @group Seller Analytics
 *
 * APIs for seller analytics and performance charts.
 * Provides time-series revenue data, sales volume,
 * top products, and month-over-month comparison.
 * All endpoints require authentication and an active seller account.
 */
class SellerAnalyticsController extends Controller
{
    /**
     * Revenue chart
     *
     * Get revenue data grouped by time period for chart rendering.
     * Returns gross revenue, commission, net earnings, and order count per period.
     *
     * @authenticated
     *
     * @queryParam period string Grouping: weekly, monthly, yearly. Default: monthly. Example: monthly
     * @queryParam from string Start date (Y-m-d). Default: 6 months ago. Example: 2025-09-01
     * @queryParam to string End date (Y-m-d). Default: today. Example: 2026-02-28
     *
     * @response 200 {"status":true,"message":"Revenue analytics","data":[{"period":"2026-01","revenue":"450.00","commission":"45.00","net":"405.00","orders":12},{"period":"2026-02","revenue":"680.00","commission":"68.00","net":"612.00","orders":18}]}
     * @response 404 {"status":false,"message":"Seller account not found"}
     */
    public function revenue(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        try {
            $seller = $this->getSeller($request);
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $period = $request->input('period', 'monthly');
            $from = $request->input('from', now()->subMonths(6)->startOfMonth()->toDateString());
            $to = $request->input('to', now()->toDateString());

            $format = match ($period) {
                'weekly' => '%x-W%v',
                'yearly' => '%Y',
                default  => '%Y-%m',
            };

            $data = SellerEarning::where('seller_id', $seller->id)
                ->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])
                ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period")
                ->selectRaw('SUM(gross_amount) as revenue')
                ->selectRaw('SUM(commission) as commission')
                ->selectRaw('SUM(net_amount) as net')
                ->selectRaw('COUNT(DISTINCT order_id) as orders')
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            return $this->success($data, 'Revenue analytics');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Sales volume chart
     *
     * Get sales volume data grouped by time period.
     * Shows units sold, keys uploaded, and remaining stock per period.
     *
     * @authenticated
     *
     * @queryParam period string Grouping: weekly, monthly, yearly. Default: monthly. Example: monthly
     * @queryParam from string Start date (Y-m-d). Default: 6 months ago. Example: 2025-09-01
     * @queryParam to string End date (Y-m-d). Default: today. Example: 2026-02-28
     *
     * @response 200 {"status":true,"message":"Sales analytics","data":{"chart":[{"period":"2026-01","units_sold":34},{"period":"2026-02","units_sold":48}],"stock":{"total_keys":200,"available":86,"sold":112,"reserved":2}}}
     * @response 404 {"status":false,"message":"Seller account not found"}
     */
    public function sales(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        try {
            $seller = $this->getSeller($request);
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $period = $request->input('period', 'monthly');
            $from = $request->input('from', now()->subMonths(6)->startOfMonth()->toDateString());
            $to = $request->input('to', now()->toDateString());

            $format = match ($period) {
                'weekly' => '%x-W%v',
                'yearly' => '%Y',
                default  => '%Y-%m',
            };

            $chart = OrderItem::where('seller_id', $seller->id)
                ->whereHas('order', fn ($q) => $q->whereIn('status', ['completed', 'processing']))
                ->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])
                ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period")
                ->selectRaw('SUM(quantity) as units_sold')
                ->selectRaw('COUNT(*) as items')
                ->selectRaw('SUM(subtotal) as revenue')
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            $offerIds = $seller->offers()->pluck('id');
            $stock = [
                'total_keys' => DB::table('seller_offer_keys')->whereIn('seller_offer_id', $offerIds)->count(),
                'available'  => DB::table('seller_offer_keys')->whereIn('seller_offer_id', $offerIds)->where('status', 'available')->count(),
                'sold'       => DB::table('seller_offer_keys')->whereIn('seller_offer_id', $offerIds)->where('status', 'sold')->count(),
                'reserved'   => DB::table('seller_offer_keys')->whereIn('seller_offer_id', $offerIds)->where('status', 'reserved')->count(),
            ];

            return $this->success([
                'chart' => $chart,
                'stock' => $stock,
            ], 'Sales analytics');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Top products
     *
     * Get the seller's best-performing products ranked by revenue.
     * Includes units sold, revenue, order count, and average rating.
     *
     * @authenticated
     *
     * @queryParam limit integer Number of products to return (max 25, default 10). Example: 10
     * @queryParam period string Time range: all, monthly, weekly. Default: all. Example: monthly
     *
     * @response 200 {"status":true,"message":"Top products","data":[{"product_id":5,"title":"Windows 11 Pro","slug":"windows-11-pro","image":"uploads/products/win11.jpg","units_sold":48,"revenue":"960.00","orders":35,"avg_rating":4.6}]}
     * @response 404 {"status":false,"message":"Seller account not found"}
     */
    public function topProducts(Request $request): JsonResponse
    {
        try {
            $seller = $this->getSeller($request);
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $limit = min($request->input('limit', 10), 25);

            $query = OrderItem::where('seller_id', $seller->id)
                ->whereHas('order', fn ($q) => $q->whereIn('status', ['completed', 'processing']));

            if ($request->input('period') === 'monthly') {
                $query->where('created_at', '>=', now()->startOfMonth());
            } elseif ($request->input('period') === 'weekly') {
                $query->where('created_at', '>=', now()->startOfWeek());
            }

            $products = $query
                ->select('product_id')
                ->selectRaw('SUM(quantity) as units_sold')
                ->selectRaw('SUM(subtotal) as revenue')
                ->selectRaw('COUNT(DISTINCT order_id) as orders')
                ->groupBy('product_id')
                ->orderByDesc('revenue')
                ->limit($limit)
                ->get();

            $productIds = $products->pluck('product_id');
            $productMap = \App\Models\Product::whereIn('id', $productIds)
                ->select('id', 'title', 'slug', 'image')
                ->get()
                ->keyBy('id');

            $ratingMap = ProductReview::whereIn('product_id', $productIds)
                ->where('status', 'approved')
                ->select('product_id')
                ->selectRaw('ROUND(AVG(rating), 1) as avg_rating')
                ->selectRaw('COUNT(*) as review_count')
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            $data = $products->map(function ($item) use ($productMap, $ratingMap) {
                $product = $productMap[$item->product_id] ?? null;
                $rating = $ratingMap[$item->product_id] ?? null;

                return [
                    'product_id'   => $item->product_id,
                    'title'        => $product?->title,
                    'slug'         => $product?->slug,
                    'image'        => $product?->image,
                    'units_sold'   => (int) $item->units_sold,
                    'revenue'      => $item->revenue,
                    'orders'       => (int) $item->orders,
                    'avg_rating'   => (float) ($rating?->avg_rating ?? 0),
                    'review_count' => (int) ($rating?->review_count ?? 0),
                ];
            });

            return $this->success($data, 'Top products');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Performance overview
     *
     * Get month-over-month comparison of key metrics.
     * Returns this month vs last month stats with percentage change.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"Overview analytics","data":{"this_month":{"revenue":"680.00","orders":18,"units_sold":48,"avg_order_value":"37.78"},"last_month":{"revenue":"450.00","orders":12,"units_sold":34,"avg_order_value":"37.50"},"change_pct":{"revenue":51.1,"orders":50.0,"units_sold":41.2},"all_time":{"total_revenue":"2450.00","total_orders":85,"total_units":320}}}
     * @response 404 {"status":false,"message":"Seller account not found"}
     */
    public function overview(Request $request): JsonResponse
    {
        try {
            $seller = $this->getSeller($request);
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $thisMonthEarnings = SellerEarning::where('seller_id', $seller->id)
                ->whereBetween('created_at', [now()->startOfMonth(), now()]);

            $lastMonthEarnings = SellerEarning::where('seller_id', $seller->id)
                ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);

            $thisMonthItems = OrderItem::where('seller_id', $seller->id)
                ->whereHas('order', fn ($q) => $q->whereIn('status', ['completed', 'processing']))
                ->whereBetween('created_at', [now()->startOfMonth(), now()]);

            $lastMonthItems = OrderItem::where('seller_id', $seller->id)
                ->whereHas('order', fn ($q) => $q->whereIn('status', ['completed', 'processing']))
                ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);

            $current = [
                'revenue'         => $thisMonthEarnings->clone()->sum('net_amount'),
                'orders'          => $thisMonthEarnings->clone()->distinct('order_id')->count('order_id'),
                'units_sold'      => (int) $thisMonthItems->clone()->sum('quantity'),
                'avg_order_value' => $thisMonthEarnings->clone()->distinct('order_id')->count('order_id') > 0
                    ? round($thisMonthEarnings->clone()->sum('net_amount') / $thisMonthEarnings->clone()->distinct('order_id')->count('order_id'), 2)
                    : 0,
            ];

            $previous = [
                'revenue'         => $lastMonthEarnings->clone()->sum('net_amount'),
                'orders'          => $lastMonthEarnings->clone()->distinct('order_id')->count('order_id'),
                'units_sold'      => (int) $lastMonthItems->clone()->sum('quantity'),
                'avg_order_value' => $lastMonthEarnings->clone()->distinct('order_id')->count('order_id') > 0
                    ? round($lastMonthEarnings->clone()->sum('net_amount') / $lastMonthEarnings->clone()->distinct('order_id')->count('order_id'), 2)
                    : 0,
            ];

            $changePct = [];
            foreach (['revenue', 'orders', 'units_sold'] as $key) {
                $changePct[$key] = $previous[$key] > 0
                    ? round(($current[$key] - $previous[$key]) / $previous[$key] * 100, 1)
                    : ($current[$key] > 0 ? 100 : 0);
            }

            $allTime = [
                'total_revenue' => SellerEarning::where('seller_id', $seller->id)->sum('net_amount'),
                'total_orders'  => SellerEarning::where('seller_id', $seller->id)->distinct('order_id')->count('order_id'),
                'total_units'   => (int) OrderItem::where('seller_id', $seller->id)
                    ->whereHas('order', fn ($q) => $q->whereIn('status', ['completed', 'processing']))
                    ->sum('quantity'),
            ];

            return $this->success([
                'this_month' => $current,
                'last_month' => $previous,
                'change_pct' => $changePct,
                'all_time'   => $allTime,
            ], 'Overview analytics');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Earnings breakdown
     *
     * Get daily earnings for a specific month. Ideal for detailed
     * calendar heatmap or daily bar chart visualization.
     *
     * @authenticated
     *
     * @queryParam month string Month in Y-m format. Default: current month. Example: 2026-02
     *
     * @response 200 {"status":true,"message":"Daily earnings","data":[{"date":"2026-02-01","revenue":"45.00","net":"40.50","orders":3},{"date":"2026-02-02","revenue":"0.00","net":"0.00","orders":0}]}
     * @response 404 {"status":false,"message":"Seller account not found"}
     */
    public function daily(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        try {
            $seller = $this->getSeller($request);
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $month = $request->input('month', now()->format('Y-m'));
            $start = Carbon::parse($month . '-01')->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $daysInMonth = $start->daysInMonth;

            $earnings = SellerEarning::where('seller_id', $seller->id)
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date')
                ->selectRaw('SUM(gross_amount) as revenue')
                ->selectRaw('SUM(net_amount) as net')
                ->selectRaw('COUNT(DISTINCT order_id) as orders')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $data = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date = $start->copy()->day($d)->toDateString();
                $row = $earnings[$date] ?? null;
                $data[] = [
                    'date'    => $date,
                    'revenue' => $row?->revenue ?? '0.00',
                    'net'     => $row?->net ?? '0.00',
                    'orders'  => (int) ($row?->orders ?? 0),
                ];
            }

            return $this->success($data, 'Daily earnings');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    private function getSeller(Request $request): ?Seller
    {
        return Seller::where('user_id', $request->user()->id)->first();
    }
}
