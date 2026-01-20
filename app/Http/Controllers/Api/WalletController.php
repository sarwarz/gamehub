<?php

namespace App\Http\Controllers\Api;

use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\WalletSetting;
use App\Models\WalletTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

/**
 * @group Wallet
 *
 * APIs for wallet balance and wallet transactions.
 */
class WalletController extends Controller
{

    /**
     * Get wallet settings
     *
     * @authenticate
     * @response 200 {
     *   "status": true,
     *   "message": "Wallet settings fetched successfully",
     *   "data": {}
     * }
     */
    public function settings()
    {
        $settings = Cache::rememberForever('wallet_settings', function () {
            return WalletSetting::global();
        });

        return $this->successResponse(
            $this->transformSettings($settings),
            'Wallet settings fetched successfully'
        );
    }


    /**
     * Get wallet details
     *
     * Retrieve the authenticated user's wallet.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Wallet fetched successfully",
     *   "data": {
     *     "balance": "150.00",
     *     "is_active": true
     *   }
     * }
     */
    public function show(Request $request)
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['balance' => 0, 'is_active' => true]
        );

        if (!$wallet->is_active) {
            return $this->errorResponse('Wallet is disabled', 403);
        }

        return $this->successResponse(
            $this->transformWallet($wallet),
            'Wallet fetched successfully'
        );
    }

    /**
     * List wallet transactions
     *
     * Retrieve wallet transaction history.
     *
     * @authenticated
     *
     * @queryParam type string Optional. credit or debit. Example: credit
     * @queryParam source string Optional. order, refund, withdraw. Example: order
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Wallet transactions fetched successfully",
     *   "data": {
     *     "current_page": 1,
     *     "data": []
     *   }
     * }
     */
    public function transactions(Request $request)
    {
        $wallet = Wallet::where('user_id', $request->user()->id)->first();

        if (!$wallet) {
            return $this->errorResponse('Wallet not found', 404);
        }

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->source, fn ($q) => $q->where('source', $request->source))
            ->latest()
            ->paginate(15);

        return $this->successResponse(
            $transactions->through(fn ($trx) => $this->transformTransaction($trx)),
            'Wallet transactions fetched successfully'
        );
    }

    /* --------------------------------
     | Transformers
     |-------------------------------- */

    protected function transformSettings(WalletSetting $settings): array
    {
        return [
            'wallet_enabled' => $settings->wallet_enabled,

            // Deposit
            'min_topup_amount' => $settings->min_topup_amount,
            'max_topup_amount' => $settings->max_topup_amount,
            'allowed_payment_gateways' => $settings->allowed_payment_gateways,
            'gateway_charge_type' => $settings->gateway_charge_type,
            'gateway_charge_amount' => $settings->gateway_charge_amount,

            // Wallet usage
            'partial_payment_enabled' => $settings->partial_payment_enabled,
            'auto_deduct_wallet_for_partial' => $settings->auto_deduct_wallet_for_partial,

            // Transfer
            'wallet_transfer_enabled' => $settings->wallet_transfer_enabled,
            'min_transfer_amount' => $settings->min_transfer_amount,
            'transfer_charge_type' => $settings->transfer_charge_type,
            'transfer_charge_amount' => $settings->transfer_charge_amount,

            // Currency
            'currency' => $settings->currency,
        ];
    }

    protected function transformWallet(Wallet $wallet): array
    {
        return [
            'id'        => $wallet->id,
            'balance'   => $wallet->balance,
            'is_active' => $wallet->is_active,
        ];
    }

    protected function transformTransaction(WalletTransaction $trx): array
    {
        return [
            'id'          => $trx->id,
            'amount'      => $trx->amount,
            'type'        => $trx->type,       // credit | debit
            'source'      => $trx->source,     // order | refund | withdraw
            'description' => $trx->description,
            'reference'   => [
                'id'   => $trx->reference_id,
                'type' => $trx->reference_type,
            ],
            'created_at'  => $trx->created_at,
        ];
    }

    /* --------------------------------
     | API Response Helpers
     |-------------------------------- */

    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function errorResponse($message, $code = 400)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
        ], $code);
    }
}
