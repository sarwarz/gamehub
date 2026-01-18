<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Coupons
 *
 * APIs for listing, viewing, and validating discount coupons.
 */
class CouponController extends Controller
{
    /**
     * List active coupons
     *
     * Retrieve all active coupons.
     *
     * @queryParam type string Optional. percent or fixed. Example: percent
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Coupons fetched successfully",
     *   "data": []
     * }
     */
    public function index(Request $request)
    {
        $coupons = Coupon::where('is_active', true)
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(10);

        return $this->successResponse($coupons, 'Coupons fetched successfully');
    }

    /**
     * Get coupon details
     *
     * Retrieve a single coupon by ID.
     *
     * @urlParam id int required Coupon ID. Example: 1
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Coupon details fetched",
     *   "data": {
     *     "code": "START70",
     *     "type": "percent",
     *     "value": 70
     *   }
     * }
     */
    public function show($id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return $this->errorResponse('Coupon not found', 404);
        }

        return $this->successResponse($coupon, 'Coupon details fetched');
    }

    /**
     * Validate coupon
     *
     * Validate a coupon code against order data.
     *
     *
     * @bodyParam code string required Coupon code. Example: START70
     * @bodyParam order_amount number required Order amount. Example: 100
     * @bodyParam category_ids array Optional Category IDs. Example: [1,2]
     * @bodyParam product_ids array Optional Product IDs. Example: [5,10]
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Coupon is valid",
     *   "data": {
     *     "discount": 70
     *   }
     * }
     */
    public function validateCoupon(Request $request)
    {
        $data = $request->validate([
            'code'         => 'required|string',
            'order_amount' => 'required|numeric|min:1',
            'category_ids' => 'nullable|array',
            'product_ids'  => 'nullable|array',
        ]);

        $coupon = Coupon::where('code', $data['code'])->first();

        if (!$coupon || !$coupon->isActive()) {
            return $this->errorResponse('Invalid or expired coupon', 422);
        }

        if ($coupon->usage_limit && $coupon->used >= $coupon->usage_limit) {
            return $this->errorResponse('Coupon usage limit reached', 422);
        }

        if ($coupon->min_order_amount && $data['order_amount'] < $coupon->min_order_amount) {
            return $this->errorResponse('Order amount is too low for this coupon', 422);
        }

        if ($coupon->max_order_amount && $data['order_amount'] > $coupon->max_order_amount) {
            return $this->errorResponse('Order amount exceeds coupon limit', 422);
        }

        // Category restrictions
        if (!empty($coupon->include_categories) &&
            empty(array_intersect($coupon->include_categories, $data['category_ids'] ?? []))) {
            return $this->errorResponse('Coupon not applicable for selected categories', 422);
        }

        if (!empty($coupon->exclude_categories) &&
            array_intersect($coupon->exclude_categories, $data['category_ids'] ?? [])) {
            return $this->errorResponse('Coupon excluded for selected categories', 422);
        }

        // Product restrictions
        if (!empty($coupon->include_products) &&
            empty(array_intersect($coupon->include_products, $data['product_ids'] ?? []))) {
            return $this->errorResponse('Coupon not applicable for selected products', 422);
        }

        if (!empty($coupon->exclude_products) &&
            array_intersect($coupon->exclude_products, $data['product_ids'] ?? [])) {
            return $this->errorResponse('Coupon excluded for selected products', 422);
        }

        // Calculate discount
        $discount = $coupon->type === 'percent'
            ? round(($coupon->value / 100) * $data['order_amount'], 2)
            : min($coupon->value, $data['order_amount']);

        return $this->successResponse([
            'discount' => $discount,
            'type'     => $coupon->type,
            'value'    => $coupon->value,
        ], 'Coupon is valid');
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
