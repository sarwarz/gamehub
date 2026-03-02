<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\SellerWithdraw;
use App\Services\SellerBalanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * @group Seller Withdrawals
 *
 * APIs for seller withdrawal requests and history.
 * All endpoints require authentication and an active seller account.
 */
class SellerWithdrawController extends Controller
{
    public function __construct(
        protected SellerBalanceService $balanceService
    ) {}

    /**
     * List withdrawals
     *
     * Retrieve paginated withdrawal history for the authenticated seller.
     *
     * @authenticated
     *
     * @queryParam status string Filter by status (pending, approved, rejected, cancelled). Example: pending
     * @queryParam per_page integer Results per page (default 10). Example: 15
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $seller = $this->getSeller();
            if (!$seller) return $this->error('Seller account not found.', 404);

            $withdraws = SellerWithdraw::where('seller_id', $seller->id)
                ->when($request->status, fn ($q) => $q->where('status', $request->status))
                ->latest()
                ->paginate(min($request->integer('per_page', 10), 50));

            return $this->success($withdraws, 'Withdrawals fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Get available payment methods
     *
     * Returns the list of supported withdrawal payment methods
     * with their required fields for the frontend form.
     *
     * @authenticated
     */
    public function methods(): JsonResponse
    {
        try {
            $allMethods = [
                'paypal' => [
                    'label'  => 'PayPal',
                    'icon'   => 'paypal',
                    'fields' => [
                        ['name' => 'email', 'label' => 'PayPal Email', 'type' => 'email', 'required' => true],
                    ],
                ],
                'bank' => [
                    'label'  => 'Bank Transfer',
                    'icon'   => 'building-bank',
                    'fields' => [
                        ['name' => 'bank_name', 'label' => 'Bank Name', 'type' => 'text', 'required' => true],
                        ['name' => 'account_name', 'label' => 'Account Holder Name', 'type' => 'text', 'required' => true],
                        ['name' => 'account_number', 'label' => 'Account Number / IBAN', 'type' => 'text', 'required' => true],
                        ['name' => 'routing_number', 'label' => 'Routing / SWIFT / BIC', 'type' => 'text', 'required' => false],
                        ['name' => 'branch_name', 'label' => 'Branch Name', 'type' => 'text', 'required' => false],
                    ],
                ],
                'crypto' => [
                    'label'  => 'Cryptocurrency',
                    'icon'   => 'currency-bitcoin',
                    'fields' => [
                        ['name' => 'network', 'label' => 'Network (BTC, ETH, USDT-TRC20, etc.)', 'type' => 'text', 'required' => true],
                        ['name' => 'wallet_address', 'label' => 'Wallet Address', 'type' => 'text', 'required' => true],
                    ],
                ],
                'bkash' => [
                    'label'  => 'bKash',
                    'icon'   => 'device-mobile',
                    'fields' => [
                        ['name' => 'phone', 'label' => 'bKash Number', 'type' => 'text', 'required' => true],
                        ['name' => 'account_type', 'label' => 'Account Type (Personal/Agent/Merchant)', 'type' => 'text', 'required' => false],
                    ],
                ],
                'nagad' => [
                    'label'  => 'Nagad',
                    'icon'   => 'device-mobile',
                    'fields' => [
                        ['name' => 'phone', 'label' => 'Nagad Number', 'type' => 'text', 'required' => true],
                    ],
                ],
                'wise' => [
                    'label'  => 'Wise (TransferWise)',
                    'icon'   => 'arrows-exchange',
                    'fields' => [
                        ['name' => 'email', 'label' => 'Wise Email', 'type' => 'email', 'required' => true],
                        ['name' => 'currency', 'label' => 'Preferred Currency', 'type' => 'text', 'required' => false],
                    ],
                ],
                'payoneer' => [
                    'label'  => 'Payoneer',
                    'icon'   => 'credit-card',
                    'fields' => [
                        ['name' => 'email', 'label' => 'Payoneer Email', 'type' => 'email', 'required' => true],
                    ],
                ],
                'skrill' => [
                    'label'  => 'Skrill',
                    'icon'   => 'wallet',
                    'fields' => [
                        ['name' => 'email', 'label' => 'Skrill Email', 'type' => 'email', 'required' => true],
                    ],
                ],
            ];

            $configuredMethods = Setting::get('vendor', 'payout_methods', []);
            if (!empty($configuredMethods) && is_array($configuredMethods)) {
                $allMethods = array_intersect_key($allMethods, array_flip($configuredMethods));
            }

            return $this->success($allMethods, 'Payment methods retrieved');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Submit withdrawal
     *
     * Create a new withdrawal request with payment details.
     * The amount is held (deducted from available_balance) immediately.
     * Only one pending withdrawal is allowed at a time.
     *
     * @authenticated
     *
     * @bodyParam amount number required Withdrawal amount (min 1.00). Example: 150.00
     * @bodyParam method string required Payment method: bank, paypal, crypto, bkash, nagad, wise, payoneer, skrill. Example: paypal
     * @bodyParam payment_details object required Payment details specific to the selected method.
     * @bodyParam payment_details.email string PayPal/Wise/Payoneer/Skrill email (required for those methods). Example: seller@example.com
     * @bodyParam payment_details.bank_name string Bank name (required for bank method). Example: Chase Bank
     * @bodyParam payment_details.account_name string Account holder name (required for bank). Example: John Doe
     * @bodyParam payment_details.account_number string Account number/IBAN (required for bank). Example: 1234567890
     * @bodyParam payment_details.routing_number string SWIFT/BIC/Routing number. Example: CHASUS33
     * @bodyParam payment_details.network string Crypto network (required for crypto). Example: USDT-TRC20
     * @bodyParam payment_details.wallet_address string Crypto wallet address (required for crypto). Example: TXyz...
     * @bodyParam payment_details.phone string Mobile number (required for bkash/nagad). Example: +8801712345678
     * @bodyParam note string optional Additional note for the admin. Example: Please process ASAP.
     */
    public function store(Request $request): JsonResponse
    {
        $vendorSettings = Setting::group('vendor');
        $minWithdrawal = (float) ($vendorSettings['min_withdrawal'] ?? 1);

        $configuredMethods = $vendorSettings['payout_methods'] ?? [];
        $allMethods = ['bank', 'paypal', 'crypto', 'bkash', 'nagad', 'wise', 'payoneer', 'skrill'];
        $allowedMethods = !empty($configuredMethods) ? array_intersect($allMethods, (array) $configuredMethods) : $allMethods;

        $data = $request->validate([
            'amount'          => "required|numeric|min:{$minWithdrawal}",
            'method'          => ['required', Rule::in($allowedMethods)],
            'payment_details' => 'required|array',
            'note'            => 'nullable|string|max:500',
        ]);

        $paymentRules = $this->getPaymentDetailRules($data['method']);
        $request->validate($paymentRules);

        try {
            $seller = $this->getSeller();
            if (!$seller) return $this->error('Seller account not found.', 404);

            $maxPending = (int) ($vendorSettings['max_pending_withdrawals'] ?? 1);

            $balance = $this->balanceService->getOrCreateBalance($seller->id);

            if ($data['amount'] > $balance->available_balance) {
                return $this->error(
                    'Insufficient balance. Available: ' . number_format($balance->available_balance, 2),
                    422
                );
            }

            $pendingCount = SellerWithdraw::where('seller_id', $seller->id)
                ->where('status', 'pending')
                ->count();

            if ($pendingCount >= $maxPending) {
                return $this->error(
                    "You already have {$pendingCount} pending withdrawal request(s). Maximum allowed: {$maxPending}.",
                    422
                );
            }

            try {
                $withdraw = DB::transaction(function () use ($seller, $data, $balance) {
                    $balance = $balance->lockForUpdate()->find($balance->id);

                    if ($data['amount'] > $balance->available_balance) {
                        throw new \RuntimeException('Insufficient balance.');
                    }

                    $balance->available_balance = bcsub($balance->available_balance, $data['amount'], 2);
                    $balance->save();

                    return SellerWithdraw::create([
                        'seller_id'       => $seller->id,
                        'amount'          => $data['amount'],
                        'method'          => $data['method'],
                        'payment_details' => $data['payment_details'],
                        'status'          => 'pending',
                        'note'            => $data['note'] ?? null,
                    ]);
                });
            } catch (\RuntimeException $e) {
                return $this->error($e->getMessage(), 422);
            }

            try {
                if (Setting::get('notifications', 'withdrawal_requested', true)) {
                    $seller->user->notify(new \App\Notifications\Seller\WithdrawalSubmittedNotification($withdraw));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Withdrawal submitted notification failed: ' . $e->getMessage());
            }

            return $this->success($withdraw, 'Withdrawal request submitted successfully', 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Get withdrawal details
     *
     * @authenticated
     * @urlParam id integer required Withdrawal ID. Example: 1
     */
    public function show($id): JsonResponse
    {
        try {
            $seller = $this->getSeller();
            if (!$seller) return $this->error('Seller account not found.', 404);

            $withdraw = SellerWithdraw::where('seller_id', $seller->id)->find($id);
            if (!$withdraw) return $this->error('Withdrawal not found.', 404);

            return $this->success($withdraw, 'Withdrawal details fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Cancel withdrawal
     *
     * Cancel a pending withdrawal request. The held amount is returned
     * to the seller's available balance.
     *
     * @authenticated
     * @urlParam id integer required Withdrawal ID. Example: 1
     */
    public function cancel($id): JsonResponse
    {
        try {
            $seller = $this->getSeller();
            if (!$seller) return $this->error('Seller account not found.', 404);

            $withdraw = SellerWithdraw::where('seller_id', $seller->id)->find($id);
            if (!$withdraw) return $this->error('Withdrawal not found.', 404);

            if ($withdraw->status !== 'pending') {
                return $this->error('Only pending withdrawals can be cancelled.', 422);
            }

            DB::transaction(function () use ($withdraw) {
                $balance = $this->balanceService->getOrCreateBalance($withdraw->seller_id);
                $balance = $balance->lockForUpdate()->find($balance->id);
                $balance->available_balance = bcadd($balance->available_balance, $withdraw->amount, 2);
                $balance->save();

                $withdraw->update(['status' => 'cancelled']);
            });

            return $this->success($withdraw->fresh(), 'Withdrawal cancelled and funds returned.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Transfer to wallet
     *
     * Transfer funds from seller available balance to the user's wallet.
     * This allows sellers to use their earnings for purchases on the platform
     * without withdrawing to an external payment method.
     *
     * The transfer is instant — the amount is deducted from the seller's
     * available balance and credited to the user wallet immediately.
     *
     * @authenticated
     *
     * @bodyParam amount number required Transfer amount (must not exceed available balance). Example: 50.00
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Successfully transferred 50.00 to your wallet.",
     *   "data": {
     *     "transferred_amount": 50.00,
     *     "seller_balance": {
     *       "available_balance": "150.00",
     *       "pending_balance": "25.00",
     *       "total_earned": "500.00"
     *     },
     *     "wallet_balance": "175.00",
     *     "transaction": {
     *       "id": 42,
     *       "amount": "50.00",
     *       "type": "credit",
     *       "source": "seller_transfer",
     *       "status": "completed",
     *       "description": "Transfer from seller balance (My Store)",
     *       "balance_after": "175.00",
     *       "created_at": "2026-03-01T12:00:00.000000Z"
     *     }
     *   }
     * }
     * @response 422 {
     *   "status": false,
     *   "message": "Insufficient seller balance. Available: 25.00"
     * }
     */
    public function transferToWallet(Request $request): JsonResponse
    {
        try {
            $seller = $this->getSeller();
            if (!$seller) {
                return $this->error('Seller account not found.', 404);
            }

            $request->validate([
                'amount' => 'required|numeric|min:0.01',
            ]);

            $transaction = $this->balanceService->transferToWallet(
                $seller,
                (float) $request->amount
            );

            $balance = $this->balanceService->getOrCreateBalance($seller->id);

            return $this->success([
                'transferred_amount' => (float) $request->amount,
                'seller_balance' => [
                    'available_balance' => $balance->available_balance,
                    'pending_balance'   => $balance->pending_balance,
                    'total_earned'      => $balance->total_earned,
                ],
                'wallet_balance' => $transaction->balance_after,
                'transaction' => [
                    'id'            => $transaction->id,
                    'amount'        => $transaction->amount,
                    'type'          => $transaction->type,
                    'source'        => $transaction->source,
                    'status'        => $transaction->status,
                    'description'   => $transaction->description,
                    'balance_after' => $transaction->balance_after,
                    'created_at'    => $transaction->created_at,
                ],
            ], "Successfully transferred " . number_format($request->amount, 2) . " to your wallet.");
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    private function getPaymentDetailRules(string $method): array
    {
        return match ($method) {
            'paypal', 'wise', 'payoneer', 'skrill' => [
                'payment_details.email' => 'required|email|max:255',
            ],
            'bank' => [
                'payment_details.bank_name'      => 'required|string|max:255',
                'payment_details.account_name'   => 'required|string|max:255',
                'payment_details.account_number'  => 'required|string|max:255',
                'payment_details.routing_number'  => 'nullable|string|max:100',
                'payment_details.branch_name'     => 'nullable|string|max:255',
            ],
            'crypto' => [
                'payment_details.network'        => 'required|string|max:100',
                'payment_details.wallet_address' => 'required|string|max:500',
            ],
            'bkash', 'nagad' => [
                'payment_details.phone' => 'required|string|max:20',
            ],
            default => [],
        };
    }

    private function getSeller(): ?Seller
    {
        return Seller::where('user_id', auth()->id())->first();
    }
}
