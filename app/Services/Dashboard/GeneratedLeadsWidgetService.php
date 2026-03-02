<?php

namespace App\Services\Dashboard;

use App\Models\OrderItem;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GeneratedLeadsWidgetService
{
    public function data($user): array
    {
        return Cache::remember('dashboard:generated-leads:admin', now()->addMinutes(10), fn () => $this->build());
    }

    protected function build(): array
    {
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd   = Carbon::now()->endOfMonth();

        $categories = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('category_product', 'category_product.product_id', '=', 'products.id')
            ->join('product_categories', 'product_categories.id', '=', 'category_product.category_id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$thisMonthStart, $thisMonthEnd])
            ->select('product_categories.name', DB::raw('COUNT(DISTINCT order_items.id) as sales_count'))
            ->groupBy('product_categories.name')
            ->orderByDesc('sales_count')
            ->limit(4)
            ->get();

        $totalSales = $categories->sum('sales_count');

        $labels = $categories->pluck('name')->toArray();
        $series = $categories->pluck('sales_count')->map(fn ($v) => (int) $v)->toArray();

        if (empty($labels)) {
            $labels = ['No Data'];
            $series = [0];
        }

        return [
            'labels'      => $labels,
            'series'      => $series,
            'total_sales' => $totalSales,
        ];
    }
}
