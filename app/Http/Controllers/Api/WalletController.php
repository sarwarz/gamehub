<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

/**
 * @group Wallet
 *
 * APIs for wallet balance and wallet transactions.
 */
class WalletController extends Controller
{
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
