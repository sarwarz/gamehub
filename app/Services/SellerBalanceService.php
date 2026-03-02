<?php

namespace App\Services;

use App\Models\Seller;
use App\Models\Setting;
use App\Models\SellerBalance;
use App\Models\SellerEarning;
use App\Models\SellerWithdraw;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

class SellerBalanceService
{
    /**
     * Get or create a seller's balance record.
     */
    public function getOrCreateBalance(int $sellerId): SellerBalance
    {
        return SellerBalance::firstOrCreate(
            ['seller_id' => $sellerId],
            ['available_balance' => 0, 'pending_balance' => 0, 'total_earned' => 0, 'total_paid' => 0]
        );
    }

    /**
     * Called when payment is confirmed (wallet or gateway).
     * Adds net earnings to pending_balance and total_earned.
     * Earnings stay status='pending' until order is delivered.
     */
    public function onPaymentConfirmed(int $orderId): void
    {
        $earnings = SellerEarning::where('order_id', $orderId)
            ->where('status', 'pending')
            ->whereNull('balance_credited_at')
            ->get();

        foreach ($earnings->groupBy('seller_id') as $sellerId => $sellerEarnings) {
            $netTotal = $sellerEarnings->sum('net_amount');

            DB::transaction(function () use ($sellerId, $netTotal, $sellerEarnings) {
                $balance = SellerBalance::lockForUpdate()->firstOrCreate(
                    ['seller_id' => $sellerId],
                    ['available_balance' => 0, 'pending_balance' => 0, 'total_earned' => 0, 'total_paid' => 0]
                );

                $balance->pending_balance = bcadd($balance->pending_balance, $netTotal, 2);
                $balance->total_earned    = bcadd($balance->total_earned, $netTotal, 2);
                $balance->save();

                SellerEarning::whereIn('id', $sellerEarnings->pluck('id'))
                    ->whereNull('balance_credited_at')
                    ->update(['balance_credited_at' => now()]);
            });
        }
    }

    /**
     * Called when an order is completed (all items delivered).
     * If escrowDays > 0, earnings enter escrow hold.
     * If escrowDays = 0 (admin action), earnings go straight to available.
     */
    public function onOrderCompleted(int $orderId, int $escrowDays = 14): void
    {
        $earnings = SellerEarning::where('order_id', $orderId)
            ->where('status', 'pending')
            ->get();

        if ($escrowDays === 0) {
            foreach ($earnings->groupBy('seller_id') as $sellerId => $sellerEarnings) {
                $netTotal = $sellerEarnings->sum('net_amount');

                DB::transaction(function () use ($sellerId, $netTotal, $sellerEarnings) {
                    $balance = SellerBalance::lockForUpdate()->firstOrCreate(
                        ['seller_id' => $sellerId],
                        ['available_balance' => 0, 'pending_balance' => 0, 'total_earned' => 0, 'total_paid' => 0]
                    );

                    $balance->pending_balance   = max(0, bcsub($balance->pending_balance, $netTotal, 2));
                    $balance->available_balance = bcadd($balance->available_balance, $netTotal, 2);
                    $balance->save();

                    SellerEarning::whereIn('id', $sellerEarnings->pluck('id'))
                        ->update([
                            'status'             => 'available',
                            'escrow_released_at' => now(),
                        ]);
                });
            }
            return;
        }

        foreach ($earnings->groupBy('seller_id') as $sellerId => $sellerEarnings) {
            DB::transaction(function () use ($sellerEarnings, $escrowDays) {
                SellerEarning::whereIn('id', $sellerEarnings->pluck('id'))
                    ->update([
                        'status'            => 'held',
                        'escrow_expires_at' => now()->addDays($escrowDays),
                    ]);
            });
        }
    }

    /**
     * Release escrow-held earnings → available_balance.
     * Called by the daily scheduled task after the escrow period expires.
     */
    public function releaseEscrow(int $orderId): void
    {
        $earnings = SellerEarning::where('order_id', $orderId)
            ->where('status', 'held')
            ->get();

        foreach ($earnings->groupBy('seller_id') as $sellerId => $sellerEarnings) {
            $netTotal = $sellerEarnings->sum('net_amount');

            DB::transaction(function () use ($sellerId, $netTotal, $sellerEarnings) {
                $balance = SellerBalance::lockForUpdate()->firstOrCreate(
                    ['seller_id' => $sellerId],
                    ['available_balance' => 0, 'pending_balance' => 0, 'total_earned' => 0, 'total_paid' => 0]
                );

                $balance->pending_balance   = max(0, bcsub($balance->pending_balance, $netTotal, 2));
                $balance->available_balance = bcadd($balance->available_balance, $netTotal, 2);
                $balance->save();

                SellerEarning::whereIn('id', $sellerEarnings->pluck('id'))
                    ->update([
                        'status'             => 'available',
                        'escrow_released_at' => now(),
                    ]);
            });
        }
    }

    /**
     * Approve a withdrawal. Since balance was already held when the
     * seller submitted the request, we only need to update total_paid
     * and change the status.
     *
     * @throws \RuntimeException
     */
    public function approveWithdrawal(SellerWithdraw $withdraw): SellerWithdraw
    {
        return DB::transaction(function () use ($withdraw) {

            $withdraw = SellerWithdraw::lockForUpdate()->findOrFail($withdraw->id);

            if ($withdraw->status !== 'pending') {
                throw new \RuntimeException('Only pending withdrawals can be approved.');
            }

            $balance = SellerBalance::where('seller_id', $withdraw->seller_id)
                ->lockForUpdate()
                ->first();

            if ($balance) {
                $balance->total_paid = bcadd($balance->total_paid, $withdraw->amount, 2);
                $balance->save();
            }

            $withdraw->update(['status' => 'approved']);

            return $withdraw->fresh();
        });
    }

    /**
     * Reject a withdrawal – refund the held amount back to available_balance.
     */
    public function rejectWithdrawal(SellerWithdraw $withdraw, ?string $reason = null): SellerWithdraw
    {
        return DB::transaction(function () use ($withdraw, $reason) {

            $withdraw = SellerWithdraw::lockForUpdate()->findOrFail($withdraw->id);

            if ($withdraw->status !== 'pending') {
                throw new \RuntimeException('Only pending withdrawals can be rejected.');
            }

            $balance = SellerBalance::where('seller_id', $withdraw->seller_id)
                ->lockForUpdate()
                ->first();

            if ($balance) {
                $balance->available_balance = bcadd($balance->available_balance, $withdraw->amount, 2);
                $balance->save();
            }

            $withdraw->update([
                'status' => 'rejected',
                'note'   => $reason ?? $withdraw->note,
            ]);

            return $withdraw->fresh();
        });
    }

    /**
     * Transfer seller available balance to the user's wallet.
     *
     * @throws \RuntimeException
     */
    public function transferToWallet(Seller $seller, float $amount): WalletTransaction
    {
        $minTransfer = (float) Setting::get('vendor', 'min_wallet_transfer', 1.00);

        if ($amount < $minTransfer) {
            throw new \RuntimeException("Minimum transfer amount is " . number_format($minTransfer, 2) . ".");
        }

        return DB::transaction(function () use ($seller, $amount) {
            $balance = SellerBalance::where('seller_id', $seller->id)
                ->lockForUpdate()
                ->first();

            if (!$balance || bccomp((string) $balance->available_balance, (string) $amount, 2) < 0) {
                throw new \RuntimeException(
                    'Insufficient seller balance. Available: '
                    . number_format($balance->available_balance ?? 0, 2)
                );
            }

            $balance->available_balance = bcsub($balance->available_balance, $amount, 2);
            $balance->save();

            $walletService = app(WalletService::class);
            $wallet = $walletService->getOrCreateWallet($seller->user);
            $walletService->ensureWalletUsable($wallet);

            $transaction = $walletService->credit(
                wallet: $wallet,
                amount: $amount,
                source: 'seller_transfer',
                description: "Transfer from seller balance ({$seller->store_name})",
                referenceId: $seller->id,
                referenceType: 'App\\Models\\Seller',
            );

            try {
                if (Setting::get('wallet_notifications', 'on_seller_transfer', true)) {
                    $seller->user->notify(new \App\Notifications\Wallet\SellerTransferNotification(
                        $amount,
                        $seller->store_name,
                        (float) $transaction->balance_after
                    ));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Seller transfer notification failed: ' . $e->getMessage());
            }

            return $transaction;
        });
    }

    /**
     * Get the commission rate for a product based on the active commission mode.
     *
     * - "fixed" mode: always uses the global commission_rate from vendor settings.
     * - "product_type" mode: uses the highest commission from the product's types,
     *   falling back to the global rate if no type has a rate set.
     */
    public static function getCommissionRate($product, ?float $default = null): float
    {
        if ($default === null) {
            $default = (float) Setting::get('vendor', 'commission_rate', 10.00);
        }

        $mode = Setting::get('vendor', 'commission_mode', 'fixed');

        if ($mode === 'product_type') {
            if (!$product->relationLoaded('types')) {
                $product->load('types');
            }

            $rate = $product->types->max('commission');

            return $rate > 0 ? (float) $rate : $default;
        }

        return $default;
    }

    /**
     * Determine if the commission is percentage-based or a fixed amount.
     */
    public static function getCommissionType(): string
    {
        return Setting::get('vendor', 'commission_type', 'percentage');
    }

    /**
     * Calculate commission amount based on the commission_type setting.
     */
    public static function calculateCommission(float $grossAmount, float $rate): float
    {
        $type = self::getCommissionType();

        if ($type === 'fixed') {
            return min($rate, $grossAmount);
        }

        return round($grossAmount * ($rate / 100), 2);
    }
}
