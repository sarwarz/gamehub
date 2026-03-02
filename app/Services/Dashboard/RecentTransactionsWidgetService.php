<?php

namespace App\Services\Dashboard;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class RecentTransactionsWidgetService
{
    public function data($user): array
    {
        return Cache::remember('dashboard:recent-transactions:admin', now()->addMinutes(5), fn () => $this->build());
    }

    protected function build(): array
    {
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd   = Carbon::now()->endOfMonth();

        $transactions = Transaction::with('user:id,name')
            ->latest()
            ->limit(7)
            ->get();

        $totalThisMonth = Transaction::whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->where('status', 'completed')
            ->count();

        return [
            'transactions'    => $transactions->map(fn ($t) => [
                'id'               => $t->id,
                'trx'              => $t->trx,
                'user_name'        => $t->user?->name ?? 'System',
                'amount'           => round((float) $t->amount, 2),
                'formatted_amount' => format_currency($t->amount),
                'type'             => $t->type,
                'category'         => $t->category,
                'status'           => $t->status,
                'payment_method'   => $t->payment_method,
                'created_at'       => $t->created_at?->diffForHumans(),
            ])->toArray(),
            'total_this_month' => $totalThisMonth,
        ];
    }
}
