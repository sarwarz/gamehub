<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\OrderItem;
use App\Models\Order;

class ViewSalesWidgetService
{
    public function data($user): array
    {
        return Cache::remember(
            "dashboard:view-sales:{$user->id}",
            now()->addMinutes(10),
            fn () => $this->build($user)
        );
    }

    protected function build($user): array
    {
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        if ($user->seller) {
            $amount = OrderItem::where('seller_id', $user->seller->id)
                ->whereHas('order', fn ($q) =>
                    $q->where('payment_status', 'paid')
                      ->whereBetween('created_at', [$start, $end])
                )
                ->sum('subtotal');

            return [
                'title'  => 'Best seller of the month',
                'amount' => $amount,
                'cta'    => route('seller.sales'),
            ];
        }

        // Admin fallback
        return [
            'title'  => 'Total sales this month',
            'amount' => Order::paid()
                ->whereBetween('created_at', [$start, $end])
                ->sum('total_amount'),
            'cta' => route('orders.index'),
        ];
    }
}
