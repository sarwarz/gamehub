<?php

namespace App\Services\Dashboard;

use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PopularProductsWidgetService
{
    public function data($user): array
    {
        $key = $user->seller
            ? "dashboard:popular-products:seller:{$user->seller->id}"
            : 'dashboard:popular-products:admin';

        return Cache::remember($key, now()->addMinutes(10), fn () => $this->build($user));
    }

    protected function build($user): array
    {
        $query = OrderItem::select(
                'product_id',
                DB::raw('SUM(subtotal) as total_sales'),
                DB::raw('SUM(quantity) as total_qty')
            )
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'))
            ->groupBy('product_id')
            ->orderByDesc('total_sales')
            ->limit(6);

        if ($user->seller) {
            $query->where('seller_id', $user->seller->id);
        }

        $items = $query->with('product:id,title,slug,image')->get();

        if ($items->isEmpty()) {
            $fallback = Product::active()
                ->select('id', 'title', 'slug', 'image')
                ->latest()
                ->limit(6)
                ->get();

            return [
                'products' => $fallback->map(fn ($p) => [
                    'id'              => $p->id,
                    'title'           => $p->title,
                    'image'           => $p->image ? asset($p->image) : null,
                    'slug'            => $p->slug,
                    'total_sales'     => 0,
                    'formatted_sales' => format_currency(0),
                    'total_qty'       => 0,
                ])->toArray(),
                'total_visitors' => 0,
            ];
        }

        $totalVisitors = $items->sum('total_qty');

        $products = $items->map(fn ($item) => [
            'id'              => $item->product_id,
            'title'           => $item->product?->title ?? 'Deleted Product',
            'image'           => $item->product?->image ? asset($item->product->image) : null,
            'slug'            => $item->product?->slug,
            'total_sales'     => round((float) $item->total_sales, 2),
            'formatted_sales' => format_currency($item->total_sales),
            'total_qty'       => (int) $item->total_qty,
        ])->toArray();

        return [
            'products'       => $products,
            'total_visitors' => $totalVisitors,
        ];
    }
}
