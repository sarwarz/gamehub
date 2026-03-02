<?php

namespace App\Services\Dashboard;

use App\Models\Order;
use App\Models\SellerEarning;
use App\Models\SellerWithdraw;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ExpensesWidgetService
{
    public function data($user): array
    {
        return Cache::remember('dashboard:expenses:admin', now()->addMinutes(10), fn () => $this->build());
    }

    protected function build(): array
    {
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd   = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd   = Carbon::now()->subMonth()->endOfMonth();

        $thisMonthRevenue = (float) Order::paid()
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->sum('total_amount');

        $thisMonthCommissions = (float) SellerEarning::whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->sum('net_amount');

        $lastMonthCommissions = (float) SellerEarning::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('net_amount');

        $platformProfit = max($thisMonthRevenue - $thisMonthCommissions, 0);

        $percentage = $thisMonthRevenue > 0
            ? round(($thisMonthCommissions / $thisMonthRevenue) * 100, 0)
            : 0;

        $diff = $thisMonthCommissions - $lastMonthCommissions;

        return [
            'amount'           => round($thisMonthCommissions, 2),
            'formatted_amount' => format_currency($thisMonthCommissions),
            'percentage'       => $percentage,
            'diff'             => round($diff, 2),
            'formatted_diff'   => format_currency(abs($diff)),
            'diff_direction'   => $diff >= 0 ? 'up' : 'down',
        ];
    }
}
