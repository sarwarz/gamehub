<?php

namespace App\Listeners;

use App\Events\OrderRefunded;
use App\Models\SellerBalance;
use App\Models\SellerEarning;
use Illuminate\Support\Facades\DB;

class RevertSellerEarningsOnRefund
{
    public function handle(OrderRefunded $event): void
    {
        $order = $event->order;

        $earnings = SellerEarning::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'held', 'available'])
            ->get();

        foreach ($earnings->groupBy('seller_id') as $sellerId => $sellerEarnings) {
            DB::transaction(function () use ($sellerId, $sellerEarnings) {
                $balance = SellerBalance::where('seller_id', $sellerId)
                    ->lockForUpdate()
                    ->first();

                if (!$balance) {
                    return;
                }

                $pendingAmount   = $sellerEarnings->whereIn('status', ['pending', 'held'])->sum('net_amount');
                $availableAmount = $sellerEarnings->where('status', 'available')->sum('net_amount');
                $netTotal        = $sellerEarnings->sum('net_amount');

                if ($pendingAmount > 0) {
                    $balance->pending_balance = max(0, bcsub($balance->pending_balance, $pendingAmount, 2));
                }

                if ($availableAmount > 0) {
                    $balance->available_balance = max(0, bcsub($balance->available_balance, $availableAmount, 2));
                }

                $balance->total_earned = max(0, bcsub($balance->total_earned, $netTotal, 2));
                $balance->save();
            });
        }

        SellerEarning::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'held', 'available'])
            ->delete();
    }
}
