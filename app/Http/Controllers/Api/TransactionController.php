<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Transactions
 *
 * APIs for viewing user transactions such as payments, earnings,
 * refunds, withdrawals, and system charges.
 */
class TransactionController extends Controller
{
    /**
     * List transactions
     *
     * Retrieve a paginated list of authenticated user's transactions.
     * Supports filtering by type, status, category, and date range.
     *
     * @authenticated
     *
     * @queryParam type string Optional. credit or debit. Example: credit
     * @queryParam status string Optional. completed, pending, failed. Example: completed
     * @queryParam category string Optional. order, withdraw, refund. Example: order
     * @queryParam from string Optional. Start date (YYYY-MM-DD). Example: 2026-01-01
     * @queryParam to string Optional. End date (YYYY-MM-DD). Example: 2026-01-31
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Transactions fetched successfully",
     *   "data": {
     *     "current_page": 1,
     *     "data": []
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        try {
            $transactions = Transaction::with(['seller'])
                ->where('user_id', $request->user()->id)
                ->when($request->type, fn ($q) => $q->where('type', $request->type))
                ->when($request->status, fn ($q) => $q->where('status', $request->status))
                ->when($request->category, fn ($q) => $q->where('category', $request->category))
                ->when($request->from, fn ($q) => $q->whereDate('created_at', '>=', $request->from))
                ->when($request->to, fn ($q) => $q->whereDate('created_at', '<=', $request->to))
                ->latest()
                ->paginate(15);

            return $this->success($transactions, 'Transactions fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch transactions.', 500);
        }
    }

    /**
     * Get transaction details
     *
     * Retrieve details of a single transaction.
     *
     * @authenticated
     *
     * @urlParam id int required Transaction ID. Example: 10
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Transaction details fetched",
     *   "data": {
     *     "id": 10,
     *     "trx": "TRX20260119001",
     *     "amount": "49.99",
     *     "status": "completed"
     *   }
     * }
     */
    public function show($id): JsonResponse
    {
        try {
            $transaction = Transaction::with([
                    'seller',
                    'reference'
                ])
                ->where('user_id', auth()->id())
                ->find($id);

            if (!$transaction) {
                return $this->error('Transaction not found', 404);
            }

            return $this->success($transaction, 'Transaction details fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch transaction details.', 500);
        }
    }
}
