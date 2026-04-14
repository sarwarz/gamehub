<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\WalletSetting;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @group Wallet
 *
 * APIs for wallet balance, deposits, payments, transfers and transactions.
 */
class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /* ================================================================
     |  READ ENDPOINTS
     | ================================================================ */

    /**
     * Get wallet details
     *
     * Retrieve the authenticated user's wallet balance and status.
     *
     * @authenticated
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $wallet = $this->walletService->getOrCreateWallet($request->user());

            return $this->success(
                $this->transformWallet($wallet),
                'Wallet fetched successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch wallet.', 500);
        }
    }

    /**
     * Get wallet summary
     *
     * Retrieve aggregated wallet stats: total credits, debits,
     * deposits, spending, refunds, transfers and pending deposits.
     *
     * @authenticated
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $wallet = $this->walletService->getOrCreateWallet($request->user());

            return $this->success(
                $this->walletService->summary($wallet),
                'Wallet summary fetched successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch wallet summary.', 500);
        }
    }

    /**
     * List wallet transactions
     *
     * Retrieve paginated wallet transaction history with optional filters.
     *
     * @authenticated
     *
     * @queryParam type string Optional. credit or debit. Example: credit
     * @queryParam source string Optional. deposit, order, refund, transfer, admin. Example: order
     * @queryParam status string Optional. pending, completed, failed. Example: completed
     * @queryParam per_page integer Optional. Items per page (max 50). Example: 15
     */
    public function transactions(Request $request): JsonResponse
    {
        $request->validate([
            'type'   => 'nullable|in:credit,debit',
            'source' => 'nullable|in:deposit,order,refund,transfer,withdraw,admin',
            'status' => 'nullable|in:pending,completed,failed',
        ]);

        try {
            $wallet = Wallet::where('user_id', $request->user()->id)->first();

            if (!$wallet) {
                return $this->error('Wallet not found', 404);
            }

            $perPage = min($request->integer('per_page', 15), 50);

            $transactions = WalletTransaction::where('wallet_id', $wallet->id)
                ->when($request->type, fn ($q) => $q->where('type', $request->type))
                ->when($request->source, fn ($q) => $q->where('source', $request->source))
                ->when($request->status, fn ($q) => $q->where('status', $request->status))
                ->latest()
                ->paginate($perPage);

            return $this->success(
                $transactions->through(fn ($trx) => $this->transformTransaction($trx)),
                'Wallet transactions fetched successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch wallet transactions.', 500);
        }
    }

    /**
     * Show single transaction
     *
     * Retrieve details of a specific wallet transaction.
     *
     * @authenticated
     *
     * @urlParam id integer required Transaction ID. Example: 42
     */
    public function transactionShow(Request $request, int $id): JsonResponse
    {
        try {
            $wallet = Wallet::where('user_id', $request->user()->id)->first();

            if (!$wallet) {
                return $this->error('Wallet not found', 404);
            }

            $transaction = WalletTransaction::where('wallet_id', $wallet->id)
                ->where('id', $id)
                ->first();

            if (!$transaction) {
                return $this->error('Transaction not found', 404);
            }

            return $this->success(
                $this->transformTransaction($transaction),
                'Transaction fetched successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch transaction.', 500);
        }
    }

    /**
     * List deposit history
     *
     * Retrieve only deposit transactions with status tracking.
     *
     * @authenticated
     *
     * @queryParam status string Optional. pending, completed, failed. Example: completed
     * @queryParam per_page integer Optional. Items per page (max 50). Example: 15
     */
    public function deposits(Request $request): JsonResponse
    {
        try {
            $wallet = Wallet::where('user_id', $request->user()->id)->first();

            if (!$wallet) {
                return $this->error('Wallet not found', 404);
            }

            $perPage = min($request->integer('per_page', 15), 50);

            $deposits = WalletTransaction::where('wallet_id', $wallet->id)
                ->where('source', 'deposit')
                ->when($request->status, fn ($q) => $q->where('status', $request->status))
                ->latest()
                ->paginate($perPage);

            return $this->success(
                $deposits->through(fn ($trx) => $this->transformTransaction($trx)),
                'Deposit history fetched successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch deposit history.', 500);
        }
    }

    /**
     * Get wallet settings
     *
     * Retrieve global wallet configuration (deposit limits, transfer rules, charges).
     *
     * @authenticated
     */
    public function settings(): JsonResponse
    {
        try {
            $settings = $this->walletService->settings();

            return $this->success(
                $this->transformSettings($settings),
                'Wallet settings fetched successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch wallet settings.', 500);
        }
    }

    /* ================================================================
     |  ACTION ENDPOINTS
     | ================================================================ */

    /**
     * Initiate deposit (top-up)
     *
     * Start a wallet deposit. Returns the amount breakdown including gateway
     * charges and a pending transaction ID to use for payment confirmation.
     *
     * @authenticated
     *
     * @bodyParam amount numeric required Deposit amount. Example: 50.00
     * @bodyParam payment_method string required Payment gateway code. Example: stripe
     */
    public function deposit(Request $request): JsonResponse
    {
        $request->validate([
            'amount'         => 'required|numeric|min:0.01|max:999999.99',
            'payment_method' => 'required|string|max:50',
        ]);

        try {
            $result = $this->walletService->initiateDeposit(
                $request->user(),
                (float) $request->amount
            );

            return $this->success(
                $result,
                'Deposit initiated successfully. Complete payment to confirm.'
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to initiate deposit.', 500);
        }
    }

    /**
     * Confirm deposit
     *
     * Confirm a pending deposit after payment is verified.
     * Typically called after payment gateway callback/webhook.
     *
     * @authenticated
     *
     * @bodyParam transaction_id integer required Pending deposit transaction ID. Example: 42
     * @bodyParam gateway_reference string optional Payment gateway reference. Example: pi_3abc123
     */
    public function confirmDeposit(Request $request): JsonResponse
    {
        $request->validate([
            'transaction_id'    => 'required|integer',
            'gateway_reference' => 'nullable|string|max:255',
        ]);

        try {
            $wallet = Wallet::where('user_id', $request->user()->id)->first();
            if (!$wallet) {
                return $this->error('Wallet not found.', 404);
            }

            $pendingTx = WalletTransaction::where('id', $request->transaction_id)
                ->where('wallet_id', $wallet->id)
                ->where('source', 'deposit')
                ->where('status', 'pending')
                ->first();

            if (!$pendingTx) {
                return $this->error('Pending deposit not found.', 404);
            }

            if (!$request->gateway_reference) {
                return $this->error('Gateway reference is required to confirm a deposit.', 422);
            }

            $transaction = $this->walletService->confirmDeposit(
                $pendingTx->id,
                $request->gateway_reference
            );

            return $this->success(
                $this->transformTransaction($transaction),
                'Deposit confirmed and wallet credited successfully'
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to confirm deposit.', 500);
        }
    }

    /**
     * Pay with wallet
     *
     * Deduct wallet balance to pay for an order (full or partial).
     *
     * @authenticated
     *
     * @bodyParam order_id string required Order ID to pay for. Example: 101
     * @bodyParam amount numeric required Amount to pay from wallet. Example: 25.50
     */
    public function pay(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|string',
            'amount'   => 'required|numeric|min:0.01|max:999999.99',
        ]);

        $order = \App\Models\Order::where('id', $request->order_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return $this->error('Order not found.', 404);
        }

        if ($order->payment_status === 'paid') {
            return $this->error('This order has already been paid.', 422);
        }

        if (!in_array($order->status, ['pending'])) {
            return $this->error('Only pending orders can be paid.', 422);
        }

        if ($request->amount > $order->total_amount) {
            return $this->error('Amount exceeds order total.', 422);
        }

        try {
            $transaction = $this->walletService->payForOrder(
                $request->user(),
                $order->id,
                (float) $request->amount
            );

            return $this->success(
                $this->transformTransaction($transaction),
                'Wallet payment successful'
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to process wallet payment.', 500);
        }
    }

    /**
     * Transfer to another user
     *
     * Transfer funds from your wallet to another user's wallet.
     * Subject to transfer charges if configured.
     *
     * @authenticated
     *
     * @bodyParam username string required Recipient's username. Example: john_doe
     * @bodyParam amount numeric required Transfer amount. Example: 25.00
     * @bodyParam note string optional Transfer note. Example: Payment for game keys
     */
    public function transfer(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string|exists:users,username',
            'amount'   => 'required|numeric|min:0.01|max:999999.99',
            'note'     => 'nullable|string|max:255',
        ]);

        $receiver = User::where('username', $request->username)->first();

        try {
            $result = $this->walletService->transfer(
                sender:   $request->user(),
                receiver: $receiver,
                amount:   (float) $request->amount,
                note:     $request->note,
            );

            return $this->success([
                'amount'         => $result['amount'],
                'charge'         => $result['charge'],
                'total_deducted' => $result['total_deducted'],
                'receiver'       => $receiver->username,
                'transaction'    => $this->transformTransaction($result['debit_transaction']),
            ], 'Transfer completed successfully');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to complete transfer.', 500);
        }
    }

    /**
     * Activate wallet
     *
     * Create and activate a wallet for the authenticated user.
     *
     * @authenticated
     */
    public function activate(Request $request): JsonResponse
    {
        try {
            if (!$this->walletService->settings()->wallet_enabled) {
                return $this->error('Wallet system is currently disabled.', 422);
            }

            $wallet = $this->walletService->getOrCreateWallet($request->user());

            if ($wallet->is_active) {
                return $this->success(
                    $this->transformWallet($wallet),
                    'Wallet is already active'
                );
            }

            $wallet->update(['is_active' => true]);

            return $this->success(
                $this->transformWallet($wallet->fresh()),
                'Wallet activated successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to activate wallet.', 500);
        }
    }

    /**
     * Request withdrawal
     *
     * Request a withdrawal from your wallet balance.
     * Subject to withdrawal fees and daily limits if configured.
     * May require admin approval depending on settings.
     *
     * @authenticated
     *
     * @bodyParam amount numeric required Withdrawal amount. Example: 50.00
     */
    public function withdraw(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999.99',
        ]);

        try {
            $transaction = $this->walletService->initiateWithdraw(
                $request->user(),
                (float) $request->amount,
            );

            $settings = $this->walletService->settings();
            $message  = $settings->auto_approve_withdraw
                ? 'Withdrawal processed successfully'
                : 'Withdrawal request submitted. Awaiting admin approval.';

            return $this->success(
                $this->transformTransaction($transaction),
                $message
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to process withdrawal.', 500);
        }
    }

    /**
     * Refund to wallet
     *
     * Wallet refunds are processed internally by the system when a refund
     * request is approved by an admin. This endpoint is not available
     * for direct user access.
     *
     * @authenticated
     * @hideFromAPIDocumentation
     */
    public function refund(Request $request): JsonResponse
    {
        return $this->error('Refunds are processed through the refund request system.', 403);
    }

    /* ================================================================
     |  TRANSFORMERS
     | ================================================================ */

    protected function transformSettings(WalletSetting $settings): array
    {
        return [
            'wallet_enabled' => $settings->wallet_enabled,

            'deposit' => [
                'enabled'             => $settings->deposit_enabled,
                'min_amount'          => $settings->min_topup_amount,
                'max_amount'          => $settings->max_topup_amount,
                'daily_limit'         => $settings->max_daily_deposit_limit,
                'allowed_gateways'    => $settings->allowed_payment_gateways,
                'fee_type'            => $settings->gateway_charge_type,
                'fee_amount'          => $settings->gateway_charge_amount,
            ],

            'usage' => [
                'partial_payment_enabled'    => $settings->partial_payment_enabled,
                'auto_deduct_wallet'         => $settings->auto_deduct_wallet_for_partial,
                'max_wallet_balance'         => $settings->max_wallet_balance,
            ],

            'transfer' => [
                'enabled'       => $settings->wallet_transfer_enabled,
                'min_amount'    => $settings->min_transfer_amount,
                'max_amount'    => $settings->max_transfer_amount,
                'daily_limit'   => $settings->max_daily_transfer_limit,
                'fee_type'      => $settings->transfer_charge_type,
                'fee_amount'    => $settings->transfer_charge_amount,
            ],

            'withdrawal' => [
                'enabled'       => $settings->withdraw_enabled,
                'min_amount'    => $settings->min_withdraw_amount,
                'max_amount'    => $settings->max_withdraw_amount,
                'daily_limit'   => $settings->max_daily_withdraw_limit,
                'fee_type'      => $settings->withdraw_charge_type,
                'fee_amount'    => $settings->withdraw_charge_amount,
                'auto_approve'  => $settings->auto_approve_withdraw,
            ],

            'notifications' => [
                'low_balance_alert' => $settings->low_balance_alert_enabled,
                'low_balance_threshold' => $settings->low_balance_threshold,
            ],

            'currency' => $settings->currency,
        ];
    }

    protected function transformWallet(Wallet $wallet): array
    {
        return [
            'id'         => $wallet->id,
            'balance'    => $wallet->balance,
            'is_active'  => $wallet->is_active,
            'created_at' => $wallet->created_at,
        ];
    }

    protected function transformTransaction(WalletTransaction $trx): array
    {
        return [
            'id'            => $trx->id,
            'amount'        => $trx->amount,
            'type'          => $trx->type,
            'source'        => $trx->source,
            'status'        => $trx->status,
            'description'   => $trx->description,
            'balance_after' => $trx->balance_after,
            'reference'     => [
                'id'   => $trx->reference_id,
                'type' => $trx->reference_type,
            ],
            'created_at' => $trx->created_at,
        ];
    }
}
