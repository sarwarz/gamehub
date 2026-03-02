<?php

namespace App\Services\Dashboard;

use App\Models\Order;
use App\Models\SellerEarning;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class RevenueReportWidgetService
{
    public function data($user): array
    {
        return Cache::remember('dashboard:revenue-report:admin', now()->addMinutes(10), fn () => $this->build());
    }

    protected function build(): array
    {
        $months     = [];
        $earnings   = [];
        $commissions = [];

        for ($i = 8; $i >= 0; $i--) {
            $date  = Carbon::now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end   = $date->copy()->endOfMonth();

            $months[] = $date->format('M');

            $revenue = (float) Order::paid()
                ->whereBetween('created_at', [$start, $end])
                ->sum('total_amount');

            $sellerPayout = (float) SellerEarning::whereBetween('created_at', [$start, $end])
                ->sum('net_amount');

            $platformEarning = max($revenue - $sellerPayout, 0);

            $earnings[]    = round($platformEarning, 0);
            $commissions[] = round(-$sellerPayout, 0);
        }

        $currentMonthRevenue = (float) Order::paid()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_amount');

        $lastMonthDaily = [];
        $thisMonthDaily = [];
        $daysInMonth    = now()->daysInMonth;

        for ($d = 1; $d <= min($daysInMonth, 11); $d++) {
            $thisDay = now()->startOfMonth()->addDays($d - 1);
            $lastDay = now()->subMonth()->startOfMonth()->addDays($d - 1);

            $thisMonthDaily[] = (int) Order::paid()
                ->whereDate('created_at', $thisDay->toDateString())
                ->sum('total_amount');

            $lastMonthDaily[] = (int) Order::paid()
                ->whereDate('created_at', $lastDay->toDateString())
                ->sum('total_amount');
        }

        return [
            'categories'           => $months,
            'earnings'             => $earnings,
            'commissions'          => $commissions,
            'current_month_total'  => round($currentMonthRevenue, 2),
            'formatted_total'      => format_currency($currentMonthRevenue),
            'budget_this_month'    => $thisMonthDaily,
            'budget_last_month'    => $lastMonthDaily,
        ];
    }
}
