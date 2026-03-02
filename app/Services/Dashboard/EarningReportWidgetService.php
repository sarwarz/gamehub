<?php

namespace App\Services\Dashboard;

use App\Models\Order;
use App\Models\SellerEarning;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class EarningReportWidgetService
{
    public function data($user): array
    {
        $key = $user->seller
            ? "dashboard:earning-report:seller:{$user->seller->id}"
            : 'dashboard:earning-report:admin';

        return Cache::remember($key, now()->addMinutes(10), fn () => $this->build($user));
    }

    protected function build($user): array
    {
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $days      = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
        $series    = [];
        $todayIndex = now()->dayOfWeekIso - 1;

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);

            if ($user->seller) {
                $amount = (float) SellerEarning::where('seller_id', $user->seller->id)
                    ->whereDate('created_at', $date->toDateString())
                    ->sum('net_amount');
            } else {
                $amount = (float) Order::paid()
                    ->whereDate('created_at', $date->toDateString())
                    ->sum('total_amount');
            }

            $series[] = round($amount, 0);
        }

        $weekTotal = array_sum($series);

        $netProfit = $user->seller
            ? $weekTotal
            : $weekTotal - (float) SellerEarning::whereBetween('created_at', [$weekStart, now()])->sum('net_amount');

        $totalIncome = $weekTotal;
        $totalExpense = $weekTotal - $netProfit;

        return [
            'categories'             => $days,
            'series'                 => $series,
            'today_index'            => $todayIndex,
            'net_profit'             => round($netProfit, 2),
            'formatted_net_profit'   => format_currency($netProfit),
            'total_income'           => round($totalIncome, 2),
            'formatted_total_income' => format_currency($totalIncome),
            'total_expense'          => round($totalExpense, 2),
            'formatted_total_expense'=> format_currency($totalExpense),
        ];
    }
}
