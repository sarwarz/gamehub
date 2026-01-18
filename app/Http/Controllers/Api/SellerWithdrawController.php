<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\SellerWithdraw;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Seller Withdrawals
 *
 * APIs for seller withdrawal requests and history.
 */
class SellerWithdrawController extends Controller
{
    /**
     * List withdrawal requests
     *
     * Retrieve withdrawal history for the authenticated seller.
     *
     * @authenticated
     *
     * @queryParam status string Optional. pending, approved, rejected, completed. Example: pending
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Withdrawals fetched successfully",
     *   "data": {
     *     "current_page": 1,
     *     "data": []
     *   }
     * }
     */
    public function index(Request $request)
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();

        if (!$seller) {
            return $this->errorResponse('Seller account not found', 404);
        }

        $withdraws = SellerWithdraw::where('seller_id', $seller->id)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10);

        return $this->successResponse($withdraws, 'Withdrawals fetched successfully');
    }

    /**
     * Submit withdrawal request
     *
     * Create a new withdrawal request for the seller.
     *
     * @authenticated
     *
     * @bodyParam amount number required Withdrawal amount. Example: 150.00
     * @bodyParam method string required Payment method (bank, paypal, crypto). Example: bank
     * @bodyParam note string Optional Additional note.
     *
     * @response 201 {
     *   "status": true,
     *   "message": "Withdrawal request submitted successfully",
     *   "data": {
     *     "status": "pending"
     *   }
     * }
     */
    public function store(Request $request)
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();

        if (!$seller) {
            return $this->errorResponse('Seller account not found', 404);
        }

        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => ['required', Rule::in(['bank', 'paypal', 'crypto'])],
            'note'   => 'nullable|string|max:500',
        ]);

        // Optional: balance check
        if ($seller->balance && $data['amount'] > $seller->balance->available_balance) {
            return $this->errorResponse('Insufficient balance', 422);
        }

        $withdraw = SellerWithdraw::create([
            'seller_id' => $seller->id,
            'amount'    => $data['amount'],
            'method'    => $data['method'],
            'status'    => 'pending',
            'note'      => $data['note'] ?? null,
        ]);

        return $this->successResponse($withdraw, 'Withdrawal request submitted successfully', 201);
    }

    /**
     * Get withdrawal details
     *
     * Retrieve a single withdrawal request.
     *
     * @authenticated
     *
     * @urlParam id int required Withdrawal ID. Example: 12
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Withdrawal details fetched",
     *   "data": {
     *     "id": 12,
     *     "amount": "150.00",
     *     "status": "pending"
     *   }
     * }
     */
    public function show($id)
    {
        $seller = Seller::where('user_id', auth()->id())->first();

        if (!$seller) {
            return $this->errorResponse('Seller account not found', 404);
        }

        $withdraw = SellerWithdraw::where('seller_id', $seller->id)
            ->find($id);

        if (!$withdraw) {
            return $this->errorResponse('Withdrawal not found', 404);
        }

        return $this->successResponse($withdraw, 'Withdrawal details fetched');
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
