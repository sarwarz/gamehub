<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Services\WalletService;

class RefundWalletPayment
{
    public function handle(OrderCancelled $event): void
    {
        try {
            $order      = $event->order;
            $walletMeta = $order->meta['wallet'] ?? null;

            if ($walletMeta && ($walletMeta['wallet_amount'] ?? 0) > 0) {
                app(WalletService::class)->refundToWallet(
                    $order->user,
                    $order->id,
                    (float) $walletMeta['wallet_amount'],
                    'Order cancelled — wallet refund'
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('RefundWalletPayment failed for order #' . $event->order->id . ': ' . $e->getMessage());
            report($e);
        }
    }
}
