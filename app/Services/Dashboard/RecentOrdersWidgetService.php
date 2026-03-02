<?php

namespace App\Services\Dashboard;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class RecentOrdersWidgetService
{
    public function data($user): array
    {
        $key = $user->seller
            ? "dashboard:recent-orders:seller:{$user->seller->id}"
            : 'dashboard:recent-orders:admin';

        return Cache::remember($key, now()->addMinutes(5), fn () => $this->build($user));
    }

    protected function build($user): array
    {
        $query = Order::with(['user:id,name,email', 'billingAddress'])
            ->latest()
            ->limit(6);

        if ($user->seller) {
            $sellerId = $user->seller->id;
            $query->whereHas('items', fn ($q) => $q->where('seller_id', $sellerId));
        }

        $orders = $query->get();

        $pending   = Order::query()->when($user->seller, fn ($q) => $q->whereHas('items', fn ($q2) => $q2->where('seller_id', $user->seller->id)))->where('status', 'pending')->count();
        $processing = Order::query()->when($user->seller, fn ($q) => $q->whereHas('items', fn ($q2) => $q2->where('seller_id', $user->seller->id)))->where('status', 'processing')->count();
        $completed = Order::query()->when($user->seller, fn ($q) => $q->whereHas('items', fn ($q2) => $q2->where('seller_id', $user->seller->id)))->where('status', 'completed')->count();

        return [
            'orders' => $orders->map(fn ($o) => [
                'id'               => $o->id,
                'order_number'     => $o->order_number,
                'customer_name'    => $o->user?->name ?? 'Guest',
                'customer_email'   => $o->user?->email,
                'total_amount'     => round((float) $o->total_amount, 2),
                'formatted_total'  => format_currency($o->total_amount),
                'status'           => $o->status,
                'payment_status'   => $o->payment_status,
                'payment_method'   => $o->payment_method,
                'created_at'       => $o->created_at?->diffForHumans(),
                'country'          => $o->billingAddress?->country,
            ])->toArray(),
            'counts' => [
                'pending'    => $pending,
                'processing' => $processing,
                'completed'  => $completed,
            ],
        ];
    }
}
