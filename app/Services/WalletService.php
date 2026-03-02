<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletSetting;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class WalletService
{
    /**
     * Get or create a wallet for the user.
     */
    public function getOrCreateWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'is_active' => true]
        );
    }

    /**
     * Get cached wallet settings (cache is handled inside the model).
     */
    public function settings(): WalletSetting
    {
        return WalletSetting::global();
    }

    /**
     * @throws \RuntimeException
     */
    public function ensureWalletUsable(Wallet $wallet): void
    {
        if (!$this->settings()->wallet_enabled) {
            throw new \RuntimeException('Wallet system is currently disabled.');
        }

        if (!$wallet->is_active) {
            throw new \RuntimeException('Your wallet is disabled.');
        }
    }

    /* ================================================================
     |  CORE CREDIT / DEBIT
     |================================================================ */

    public function credit(
        Wallet  $wallet,
        float   $amount,
        string  $source,
        string  $description,
        ?int    $referenceId = null,
        ?string $referenceType = null,
        string  $status = 'completed'
    ): WalletTransaction {
        return DB::transaction(function () use ($wallet, $amount, $source, $description, $referenceId, $referenceType, $status) {

            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            $settings = $this->settings();

            if ($status === 'completed' && $settings->max_wallet_balance) {
                $projectedBalance = bcadd($wallet->balance, $amount, 2);
                if ($projectedBalance > $settings->max_wallet_balance) {
                    throw new \RuntimeException(
                        "This would exceed the maximum wallet balance of {$settings->currency} {$settings->max_wallet_balance}."
                    );
                }
            }

            if ($status === 'completed') {
                $wallet->balance = bcadd($wallet->balance, $amount, 2);
                $wallet->save();
            }

            return WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'amount'         => $amount,
                'type'           => 'credit',
                'source'         => $source,
                'description'    => $description,
                'reference_id'   => $referenceId,
                'reference_type' => $referenceType,
                'status'         => $status,
                'balance_after'  => $wallet->balance,
            ]);
        });
    }

    /**
     * @throws \RuntimeException
     */
    public function debit(
        Wallet  $wallet,
        float   $amount,
        string  $source,
        string  $description,
        ?int    $referenceId = null,
        ?string $referenceType = null,
        string  $status = 'completed'
    ): WalletTransaction {
        return DB::transaction(function () use ($wallet, $amount, $source, $description, $referenceId, $referenceType, $status) {

            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            if ($wallet->balance < $amount) {
                throw new \RuntimeException('Insufficient wallet balance.');
            }

            if ($status === 'completed') {
                $wallet->balance = bcsub($wallet->balance, $amount, 2);
                $wallet->save();
            }

            return WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'amount'         => $amount,
                'type'           => 'debit',
                'source'         => $source,
                'description'    => $description,
                'reference_id'   => $referenceId,
                'reference_type' => $referenceType,
                'status'         => $status,
                'balance_after'  => $wallet->balance,
            ]);
        });
    }

    /* ================================================================
     |  DEPOSIT
     |================================================================ */

    /**
     * @throws \RuntimeException
     */
    public function initiateDeposit(User $user, float $amount): array
    {
        $settings = $this->settings();
        $wallet   = $this->getOrCreateWallet($user);

        $this->ensureWalletUsable($wallet);

        if (!$settings->deposit_enabled) {
            throw new \RuntimeException('Wallet deposits are currently disabled.');
        }

        if ($settings->min_topup_amount && $amount < $settings->min_topup_amount) {
            throw new \RuntimeException("Minimum deposit amount is {$settings->currency} {$settings->min_topup_amount}.");
        }

        if ($settings->max_topup_amount && $amount > $settings->max_topup_amount) {
            throw new \RuntimeException("Maximum deposit amount is {$settings->currency} {$settings->max_topup_amount}.");
        }

        if ($settings->max_daily_deposit_limit) {
            $todayDeposits = $this->todayTotal($wallet->id, 'credit', 'deposit');
            $projected = bcadd($todayDeposits, $amount, 2);
            if ($projected > $settings->max_daily_deposit_limit) {
                $remaining = bcsub($settings->max_daily_deposit_limit, $todayDeposits, 2);
                throw new \RuntimeException(
                    "Daily deposit limit is {$settings->currency} {$settings->max_daily_deposit_limit}. "
                    . "Remaining today: {$settings->currency} {$remaining}."
                );
            }
        }

        if ($settings->max_wallet_balance) {
            $projectedBalance = bcadd($wallet->balance, $amount, 2);
            if ($projectedBalance > $settings->max_wallet_balance) {
                throw new \RuntimeException(
                    "This deposit would exceed the maximum wallet balance of {$settings->currency} {$settings->max_wallet_balance}."
                );
            }
        }

        $charge       = $this->calculateGatewayCharge($amount, $settings);
        $totalPayable = bcadd($amount, $charge, 2);

        $transaction = $this->credit(
            wallet: $wallet,
            amount: $amount,
            source: 'deposit',
            description: 'Wallet top-up via payment gateway',
            status: 'pending'
        );

        return [
            'transaction_id' => $transaction->id,
            'deposit_amount' => $amount,
            'gateway_charge' => $charge,
            'total_payable'  => $totalPayable,
            'currency'       => $settings->currency,
        ];
    }

    /**
     * @throws \RuntimeException
     */
    public function confirmDeposit(int $transactionId, ?string $gatewayReference = null): WalletTransaction
    {
        return DB::transaction(function () use ($transactionId, $gatewayReference) {

            $transaction = WalletTransaction::where('id', $transactionId)
                ->where('source', 'deposit')
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                throw new \RuntimeException('Pending deposit transaction not found.');
            }

            $wallet = Wallet::where('id', $transaction->wallet_id)->lockForUpdate()->first();

            $wallet->balance = bcadd($wallet->balance, $transaction->amount, 2);
            $wallet->save();

            $transaction->update([
                'status'         => 'completed',
                'balance_after'  => $wallet->balance,
                'reference_type' => $gatewayReference ? 'gateway' : $transaction->reference_type,
                'description'    => $gatewayReference
                    ? "Wallet top-up confirmed. Ref: {$gatewayReference}"
                    : 'Wallet top-up confirmed.',
            ]);

            // Notify user of confirmed deposit
            try {
                $user = $wallet->user;
                if ($user && Setting::get('wallet_notifications', 'on_deposit_confirmed', true)) {
                    $user->notify(new \App\Notifications\Wallet\WalletDepositConfirmedNotification(
                        (float) $transaction->amount,
                        (float) $wallet->balance
                    ));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Deposit notification failed: ' . $e->getMessage());
            }

            return $transaction->fresh();
        });
    }

    public function failDeposit(int $transactionId): WalletTransaction
    {
        return DB::transaction(function () use ($transactionId) {

            $transaction = WalletTransaction::where('id', $transactionId)
                ->where('source', 'deposit')
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                throw new \RuntimeException('Pending deposit transaction not found.');
            }

            $transaction->update([
                'status'      => 'failed',
                'description' => 'Wallet top-up payment failed.',
            ]);

            return $transaction->fresh();
        });
    }

    /* ================================================================
     |  ORDER PAYMENT
     |================================================================ */

    /**
     * @throws \RuntimeException
     */
    public function payForOrder(User $user, int $orderId, float $amount): WalletTransaction
    {
        $wallet = $this->getOrCreateWallet($user);
        $this->ensureWalletUsable($wallet);

        return $this->debit(
            wallet: $wallet,
            amount: $amount,
            source: 'order',
            description: "Payment for order #{$orderId}",
            referenceId: $orderId,
            referenceType: 'App\\Models\\Order',
        );
    }

    /**
     * Confirm a pending wallet hold (from checkout session) and link it to the order.
     */
    public function confirmWalletHold(int $transactionId, int $orderId): WalletTransaction
    {
        return DB::transaction(function () use ($transactionId, $orderId) {

            $transaction = WalletTransaction::where('id', $transactionId)
                ->where('source', 'order')
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                throw new \RuntimeException('Pending wallet hold transaction not found.');
            }

            $wallet = Wallet::where('id', $transaction->wallet_id)->lockForUpdate()->first();

            $wallet->balance = bcsub($wallet->balance, $transaction->amount, 2);
            $wallet->save();

            $transaction->update([
                'status'         => 'completed',
                'balance_after'  => $wallet->balance,
                'reference_id'   => $orderId,
                'reference_type' => 'App\\Models\\Order',
                'description'    => "Wallet payment for order #{$orderId}",
            ]);

            return $transaction->fresh();
        });
    }

    /**
     * Refund an order amount back to wallet.
     */
    public function refundToWallet(User $user, int $orderId, float $amount, ?string $reason = null): WalletTransaction
    {
        $wallet = $this->getOrCreateWallet($user);

        return $this->credit(
            wallet: $wallet,
            amount: $amount,
            source: 'refund',
            description: $reason ?? "Refund for order #{$orderId}",
            referenceId: $orderId,
            referenceType: 'App\\Models\\Order',
        );
    }

    /* ================================================================
     |  TRANSFER
     |================================================================ */

    /**
     * @throws \RuntimeException
     */
    public function transfer(User $sender, User $receiver, float $amount, ?string $note = null): array
    {
        $settings = $this->settings();

        if (!$settings->wallet_transfer_enabled) {
            throw new \RuntimeException('Wallet transfers are currently disabled.');
        }

        if ($sender->id === $receiver->id) {
            throw new \RuntimeException('You cannot transfer to yourself.');
        }

        if ($settings->min_transfer_amount && $amount < $settings->min_transfer_amount) {
            throw new \RuntimeException("Minimum transfer amount is {$settings->currency} {$settings->min_transfer_amount}.");
        }

        if ($settings->max_transfer_amount && $amount > $settings->max_transfer_amount) {
            throw new \RuntimeException("Maximum transfer amount is {$settings->currency} {$settings->max_transfer_amount}.");
        }

        $senderWallet   = $this->getOrCreateWallet($sender);
        $receiverWallet = $this->getOrCreateWallet($receiver);

        $this->ensureWalletUsable($senderWallet);
        $this->ensureWalletUsable($receiverWallet);

        if ($settings->max_daily_transfer_limit) {
            $todayTransfers = $this->todayTotal($senderWallet->id, 'debit', 'transfer');
            $projected = bcadd($todayTransfers, $amount, 2);
            if ($projected > $settings->max_daily_transfer_limit) {
                $remaining = bcsub($settings->max_daily_transfer_limit, $todayTransfers, 2);
                throw new \RuntimeException(
                    "Daily transfer limit is {$settings->currency} {$settings->max_daily_transfer_limit}. "
                    . "Remaining today: {$settings->currency} {$remaining}."
                );
            }
        }

        if ($settings->max_wallet_balance) {
            $receiverProjected = bcadd($receiverWallet->balance, $amount, 2);
            if ($receiverProjected > $settings->max_wallet_balance) {
                throw new \RuntimeException("Transfer would exceed the receiver's maximum wallet balance.");
            }
        }

        $charge        = $this->calculateTransferCharge($amount, $settings);
        $totalDeducted = bcadd($amount, $charge, 2);

        return DB::transaction(function () use ($senderWallet, $receiverWallet, $sender, $receiver, $amount, $charge, $totalDeducted, $note) {

            // Lock in deterministic ID order to prevent deadlocks
            $ids = [$senderWallet->id, $receiverWallet->id];
            sort($ids);
            $wallets = Wallet::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $senderWallet   = $wallets[$senderWallet->id];
            $receiverWallet = $wallets[$receiverWallet->id];

            if (bccomp((string) $senderWallet->balance, (string) $totalDeducted, 2) < 0) {
                throw new \RuntimeException('Insufficient wallet balance (including transfer fee).');
            }

            $senderWallet->balance = bcsub($senderWallet->balance, $totalDeducted, 2);
            $senderWallet->save();

            $receiverWallet->balance = bcadd($receiverWallet->balance, $amount, 2);
            $receiverWallet->save();

            $debitTrx = WalletTransaction::create([
                'wallet_id'      => $senderWallet->id,
                'amount'         => $totalDeducted,
                'type'           => 'debit',
                'source'         => 'transfer',
                'description'    => $note ?? "Transfer to {$receiver->name}",
                'reference_id'   => $receiverWallet->user_id,
                'reference_type' => 'App\\Models\\User',
                'status'         => 'completed',
                'balance_after'  => $senderWallet->balance,
            ]);

            $creditTrx = WalletTransaction::create([
                'wallet_id'      => $receiverWallet->id,
                'amount'         => $amount,
                'type'           => 'credit',
                'source'         => 'transfer',
                'description'    => $note ?? "Transfer from {$sender->name}",
                'reference_id'   => $senderWallet->user_id,
                'reference_type' => 'App\\Models\\User',
                'status'         => 'completed',
                'balance_after'  => $receiverWallet->balance,
            ]);

            // Notify receiver of incoming transfer
            try {
                if (Setting::get('wallet_notifications', 'on_transfer_received', true)) {
                    $receiver->notify(new \App\Notifications\Wallet\WalletTransferReceivedNotification(
                        $amount,
                        $sender->name,
                        (float) $receiverWallet->balance
                    ));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Transfer received notification failed: ' . $e->getMessage());
            }

            return [
                'debit_transaction'  => $debitTrx,
                'credit_transaction' => $creditTrx,
                'amount'             => $amount,
                'charge'             => $charge,
                'total_deducted'     => $totalDeducted,
            ];
        });
    }

    /* ================================================================
     |  WITHDRAWAL
     |================================================================ */

    /**
     * Initiate a withdrawal request.
     *
     * @throws \RuntimeException
     */
    public function initiateWithdraw(User $user, float $amount): WalletTransaction
    {
        $settings = $this->settings();
        $wallet   = $this->getOrCreateWallet($user);

        $this->ensureWalletUsable($wallet);

        if (!$settings->withdraw_enabled) {
            throw new \RuntimeException('Withdrawals are currently disabled.');
        }

        if ($settings->min_withdraw_amount && $amount < $settings->min_withdraw_amount) {
            throw new \RuntimeException("Minimum withdrawal is {$settings->currency} {$settings->min_withdraw_amount}.");
        }

        if ($settings->max_withdraw_amount && $amount > $settings->max_withdraw_amount) {
            throw new \RuntimeException("Maximum withdrawal is {$settings->currency} {$settings->max_withdraw_amount}.");
        }

        if ($settings->max_daily_withdraw_limit) {
            $todayWithdrawn = $this->todayTotal($wallet->id, 'debit', 'withdraw');
            $projected = bcadd($todayWithdrawn, $amount, 2);
            if ($projected > $settings->max_daily_withdraw_limit) {
                $remaining = bcsub($settings->max_daily_withdraw_limit, $todayWithdrawn, 2);
                throw new \RuntimeException(
                    "Daily withdrawal limit is {$settings->currency} {$settings->max_daily_withdraw_limit}. "
                    . "Remaining today: {$settings->currency} {$remaining}."
                );
            }
        }

        $charge        = $this->calculateWithdrawCharge($amount, $settings);
        $totalDeducted = bcadd($amount, $charge, 2);

        if ($wallet->balance < $totalDeducted) {
            throw new \RuntimeException(
                "Insufficient balance. You need {$settings->currency} {$totalDeducted} "
                . "(amount + {$settings->currency} {$charge} fee) but have {$settings->currency} {$wallet->balance}."
            );
        }

        $status = $settings->auto_approve_withdraw ? 'completed' : 'pending';

        return $this->debit(
            wallet: $wallet,
            amount: $totalDeducted,
            source: 'withdraw',
            description: $settings->auto_approve_withdraw
                ? "Withdrawal of {$settings->currency} {$amount} (fee: {$settings->currency} {$charge})"
                : "Withdrawal request of {$settings->currency} {$amount} (fee: {$settings->currency} {$charge}) – awaiting approval",
            status: $status,
        );
    }

    /**
     * Approve a pending withdrawal.
     */
    public function approveWithdraw(int $transactionId): WalletTransaction
    {
        return DB::transaction(function () use ($transactionId) {

            $transaction = WalletTransaction::where('id', $transactionId)
                ->where('source', 'withdraw')
                ->where('status', 'pending')
                ->lockForUpdate()
                ->firstOrFail();

            $wallet = Wallet::where('id', $transaction->wallet_id)->lockForUpdate()->first();

            if ($wallet->balance < $transaction->amount) {
                throw new \RuntimeException('Insufficient balance to fulfil withdrawal.');
            }

            $wallet->balance = bcsub($wallet->balance, $transaction->amount, 2);
            $wallet->save();

            $transaction->update([
                'status'        => 'completed',
                'balance_after' => $wallet->balance,
                'description'   => str_replace('awaiting approval', 'approved', $transaction->description),
            ]);

            return $transaction->fresh();
        });
    }

    /**
     * Reject / cancel a pending withdrawal.
     */
    public function rejectWithdraw(int $transactionId, ?string $reason = null): WalletTransaction
    {
        return DB::transaction(function () use ($transactionId, $reason) {

            $transaction = WalletTransaction::where('id', $transactionId)
                ->where('source', 'withdraw')
                ->where('status', 'pending')
                ->lockForUpdate()
                ->firstOrFail();

            $transaction->update([
                'status'      => 'failed',
                'description' => $reason
                    ? "Withdrawal rejected: {$reason}"
                    : 'Withdrawal rejected by admin.',
            ]);

            return $transaction->fresh();
        });
    }

    /* ================================================================
     |  SUMMARY
     |================================================================ */

    public function summary(Wallet $wallet): array
    {
        $stats = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('status', 'completed')
            ->selectRaw("
                SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_credited,
                SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_debited,
                SUM(CASE WHEN type = 'credit' AND source = 'deposit' THEN amount ELSE 0 END) as total_deposits,
                SUM(CASE WHEN type = 'debit' AND source = 'order' THEN amount ELSE 0 END) as total_spent,
                SUM(CASE WHEN type = 'credit' AND source = 'refund' THEN amount ELSE 0 END) as total_refunds,
                SUM(CASE WHEN type = 'debit' AND source = 'transfer' THEN amount ELSE 0 END) as total_transfers_sent,
                SUM(CASE WHEN type = 'credit' AND source = 'transfer' THEN amount ELSE 0 END) as total_transfers_received,
                SUM(CASE WHEN type = 'debit' AND source = 'withdraw' THEN amount ELSE 0 END) as total_withdrawn,
                SUM(CASE WHEN type = 'credit' AND source = 'seller_transfer' THEN amount ELSE 0 END) as total_seller_transfers,
                COUNT(*) as total_transactions
            ")
            ->first();

        $pendingDeposits = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('source', 'deposit')
            ->where('status', 'pending')
            ->sum('amount');

        $pendingWithdrawals = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('source', 'withdraw')
            ->where('status', 'pending')
            ->sum('amount');

        $lastTransaction = WalletTransaction::where('wallet_id', $wallet->id)
            ->latest()
            ->first();

        return [
            'balance'                  => $wallet->balance,
            'total_credited'           => $stats->total_credited ?? '0.00',
            'total_debited'            => $stats->total_debited ?? '0.00',
            'total_deposits'           => $stats->total_deposits ?? '0.00',
            'total_spent'              => $stats->total_spent ?? '0.00',
            'total_refunds'            => $stats->total_refunds ?? '0.00',
            'total_transfers_sent'     => $stats->total_transfers_sent ?? '0.00',
            'total_transfers_received' => $stats->total_transfers_received ?? '0.00',
            'total_withdrawn'          => $stats->total_withdrawn ?? '0.00',
            'total_seller_transfers'   => $stats->total_seller_transfers ?? '0.00',
            'pending_deposits'         => $pendingDeposits ?? '0.00',
            'pending_withdrawals'      => $pendingWithdrawals ?? '0.00',
            'total_transactions'       => $stats->total_transactions ?? 0,
            'last_transaction_at'      => $lastTransaction?->created_at,
        ];
    }

    /* ================================================================
     |  CHARGE CALCULATORS
     |================================================================ */

    public function calculateGatewayCharge(float $amount, ?WalletSetting $settings = null): float
    {
        $settings ??= $this->settings();

        if (!$settings->gateway_charge_amount || $settings->gateway_charge_amount <= 0) {
            return 0;
        }

        return $settings->gateway_charge_type === 'percentage'
            ? round(($amount * $settings->gateway_charge_amount) / 100, 2)
            : (float) $settings->gateway_charge_amount;
    }

    public function calculateTransferCharge(float $amount, ?WalletSetting $settings = null): float
    {
        $settings ??= $this->settings();

        if (!$settings->transfer_charge_amount || $settings->transfer_charge_amount <= 0) {
            return 0;
        }

        return $settings->transfer_charge_type === 'percentage'
            ? round(($amount * $settings->transfer_charge_amount) / 100, 2)
            : (float) $settings->transfer_charge_amount;
    }

    public function calculateWithdrawCharge(float $amount, ?WalletSetting $settings = null): float
    {
        $settings ??= $this->settings();

        if (!$settings->withdraw_charge_amount || $settings->withdraw_charge_amount <= 0) {
            return 0;
        }

        return $settings->withdraw_charge_type === 'percentage'
            ? round(($amount * $settings->withdraw_charge_amount) / 100, 2)
            : (float) $settings->withdraw_charge_amount;
    }

    /* ================================================================
     |  HELPERS
     |================================================================ */

    /**
     * Sum of completed transactions for a wallet, filtered by type and source, for today.
     */
    private function todayTotal(int $walletId, string $type, string $source): float
    {
        return (float) WalletTransaction::where('wallet_id', $walletId)
            ->where('type', $type)
            ->where('source', $source)
            ->whereIn('status', ['completed', 'pending'])
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');
    }
}
