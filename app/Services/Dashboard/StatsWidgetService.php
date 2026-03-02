<?php

namespace App\Services\Dashboard;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\SellerEarning;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class StatsWidgetService
{
    public function data($user): array
    {
        $context = $this->contextKey($user);

        return Cache::remember(
            "dashboard:statistics:{$context}",
            now()->addMinutes(10),
            fn () => $this->build($user)
        );
    }

    /**
     * Generate a SAFE cache key per user context
     */
    protected function contextKey($user): string
    {
        if ($user->seller) {
            return "seller:{$user->seller->id}";
        }

        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            return "admin";
        }

        return "user:{$user->id}";
    }

    protected function build($user): array
    {
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        /* =========================
           SELLER (TOP PRIORITY)
        ==========================*/
        if ($user->seller) {

            $sellerId = $user->seller->id;

            $sales = OrderItem::where('seller_id', $sellerId)
                ->whereHas('order', fn ($q) =>
                    $q->where('payment_status', 'paid')
                      ->whereBetween('created_at', [$start, $end])
                )
                ->sum('subtotal');

            $earnings = SellerEarning::where('seller_id', $sellerId)
                ->where('status', 'completed')
                ->sum('net_amount');

            return [
                'sales'     => format_currency($sales),
                'customers' => OrderItem::where('seller_id', $sellerId)
                    ->distinct('order_id')
                    ->count('order_id'),
                'products'  => $user->seller->offers()->count(),
                'revenue'   => format_currency($earnings),
            ];
        }

        /* =========================
           ADMIN / SUPERADMIN
        ==========================*/
        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {

            $sales = Order::paid()
                ->whereBetween('created_at', [$start, $end])
                ->sum('total_amount');

            $sellerEarnings = (float) SellerEarning::whereBetween('created_at', [$start, $end])
                ->sum('net_amount');

            $commission = max($sales - $sellerEarnings, 0);

            return [
                'sales'     => format_currency($sales),
                'customers' => User::customers()->count(),
                'products'  => Product::active()->count(),
                'revenue'   => format_currency($commission),
            ];
        }

        /* =========================
           NORMAL USER
        ==========================*/
        return [
            'sales'     => format_currency(0),
            'customers' => 0,
            'products'  => 0,
            'revenue'   => format_currency(0),
        ];
    }
}
