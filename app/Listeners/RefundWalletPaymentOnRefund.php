<?php

namespace App\Listeners;

use App\Events\OrderRefunded;
use App\Services\WalletService;

class RefundWalletPaymentOnRefund
{
    public function handle(OrderRefunded $event): void
    {
        $order      = $event->order;
        $walletMeta = $order->meta['wallet'] ?? null;

        if ($walletMeta && ($walletMeta['wallet_amount'] ?? 0) > 0) {
            $walletRefundAmount = min($event->amount, (float) $walletMeta['wallet_amount']);

            app(WalletService::class)->refundToWallet(
                $order->user,
                $order->id,
                $walletRefundAmount,
                'Order refunded — wallet refund'
            );
        }
    }
}
