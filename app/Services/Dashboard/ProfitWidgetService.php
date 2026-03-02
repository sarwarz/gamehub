<?php

namespace App\Services\Dashboard;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellerEarning;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ProfitWidgetService
{
    public function data($user): array
    {
        $key = $user->seller
            ? "dashboard:profit:seller:{$user->seller->id}"
            : 'dashboard:profit:admin';

        return Cache::remember($key, now()->addMinutes(10), fn () => $this->build($user));
    }

    protected function build($user): array
    {
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd   = Carbon::now()->subMonth()->endOfMonth();
        $prevMonthStart = Carbon::now()->subMonths(2)->startOfMonth();
        $prevMonthEnd   = Carbon::now()->subMonths(2)->endOfMonth();

        if ($user->seller) {
            $sellerId = $user->seller->id;

            $lastMonth = SellerEarning::where('seller_id', $sellerId)
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->sum('net_amount');

            $prevMonth = SellerEarning::where('seller_id', $sellerId)
                ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
                ->sum('net_amount');

            $daily = SellerEarning::where('seller_id', $sellerId)
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw('DAY(created_at) as day, SUM(net_amount) as total')
                ->groupBy('day')
                ->orderBy('day')
                ->pluck('total')
                ->map(fn ($v) => round((float) $v, 2))
                ->values()
                ->toArray();
        } else {
            $lastMonth = Order::paid()
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->sum('total_amount');

            $prevMonth = Order::paid()
                ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
                ->sum('total_amount');

            $daily = Order::paid()
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw('DAY(created_at) as day, SUM(total_amount) as total')
                ->groupBy('day')
                ->orderBy('day')
                ->pluck('total')
                ->map(fn ($v) => round((float) $v, 2))
                ->values()
                ->toArray();
        }

        $change = $prevMonth > 0
            ? round((($lastMonth - $prevMonth) / $prevMonth) * 100, 1)
            : 0;

        return [
            'amount'           => round((float) $lastMonth, 2),
            'formatted_amount' => format_currency($lastMonth),
            'change'           => $change,
            'series'           => $daily ?: [0],
        ];
    }
}
