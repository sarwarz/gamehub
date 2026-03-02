<?php

namespace App\Http\Controllers\Api;

use App\Models\Seller;
use App\Models\Coupon;
use App\Models\SellerOffer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

/**
 * @group Seller Coupons
 *
 * APIs for sellers to create and manage their own discount coupons.
 * Seller coupons only apply to products where the seller has active offers.
 *
 * All endpoints require authentication and an active seller account.
 */
class SellerCouponController extends Controller
{
    /**
     * List my coupons
     *
     * Get all coupons created by the authenticated seller.
     *
     * @authenticated
     *
     * @queryParam status string Filter: active, inactive, expired. Example: active
     * @queryParam per_page integer Results per page (default 15). Example: 10
     *
     * @response 200 {"status":true,"message":"Coupons fetched","data":{"current_page":1,"data":[],"total":0}}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $query = Coupon::where('seller_id', $seller->id);

            if ($request->status === 'active') {
                $query->where('is_active', true)
                      ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<', now());
            }

            $coupons = $query->latest()->paginate(min($request->integer('per_page', 15), 50));

            return $this->success($coupons, 'Coupons fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Create coupon
     *
     * Create a new seller coupon. The coupon is automatically scoped to the seller.
     *
     * @authenticated
     *
     * @bodyParam code string required Unique coupon code (3-30 chars, alphanumeric + dashes). Example: SUMMER2026
     * @bodyParam type string required Discount type: fixed or percent. Example: percent
     * @bodyParam value number required Discount value. Example: 15
     * @bodyParam max_discount_amount number Optional maximum discount cap for percent coupons. Example: 50.00
     * @bodyParam description string Optional description for the coupon. Example: Summer sale discount
     * @bodyParam min_order_amount number Optional minimum order amount. Example: 10.00
     * @bodyParam max_order_amount number Optional maximum order amount. Example: 500.00
     * @bodyParam usage_limit integer Optional total usage limit. Example: 100
     * @bodyParam usage_limit_per_user integer Optional per-user limit. Example: 1
     * @bodyParam starts_at date Optional start date (YYYY-MM-DD). Example: 2026-03-01
     * @bodyParam expires_at date Optional expiry date (YYYY-MM-DD). Example: 2026-06-30
     * @bodyParam include_products array Optional. Limit to specific product IDs (must be your products). Example: [5, 10]
     *
     * @response 201 {"status":true,"message":"Coupon created","data":{"id":1,"code":"SUMMER2026","type":"percent","value":"15.00","seller_id":1}}
     * @response 422 {"status":false,"message":"The code has already been taken."}
     */
    public function store(Request $request): JsonResponse
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();
        if (!$seller) {
            return $this->error('Seller account not found', 404);
        }

        $request->validate([
            'code'                 => 'required|string|min:3|max:30|regex:/^[A-Za-z0-9\-]+$/|unique:coupons,code',
            'type'                 => 'required|in:fixed,percent',
            'value'                => 'required|numeric|min:0.01',
            'max_discount_amount'  => 'nullable|numeric|min:0.01',
            'description'          => 'nullable|string|max:255',
            'min_order_amount'     => 'nullable|numeric|min:0',
            'max_order_amount'     => 'nullable|numeric|min:0',
            'usage_limit'          => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'starts_at'            => 'nullable|date',
            'expires_at'           => 'nullable|date|after_or_equal:starts_at',
            'include_products'     => 'nullable|array',
            'include_products.*'   => 'integer',
        ]);

        try {
            if ($request->type === 'percent' && $request->value > 100) {
                return $this->error('Percent discount cannot exceed 100', 422);
            }

            if (!empty($request->include_products)) {
                $sellerProductIds = SellerOffer::where('seller_id', $seller->id)
                    ->where('status', 'active')
                    ->pluck('product_id')
                    ->unique()
                    ->toArray();

                $invalid = array_diff($request->include_products, $sellerProductIds);
                if (!empty($invalid)) {
                    return $this->error('Some product IDs do not belong to your active offers: ' . implode(', ', $invalid), 422);
                }
            }

            $coupon = Coupon::create([
                'seller_id'            => $seller->id,
                'code'                 => strtoupper($request->code),
                'description'          => $request->description,
                'type'                 => $request->type,
                'value'                => $request->value,
                'max_discount_amount'  => $request->max_discount_amount,
                'min_order_amount'     => $request->min_order_amount,
                'max_order_amount'     => $request->max_order_amount,
                'usage_limit'          => $request->usage_limit,
                'usage_limit_per_user' => $request->usage_limit_per_user,
                'starts_at'            => $request->starts_at,
                'expires_at'           => $request->expires_at,
                'include_products'     => $request->include_products,
                'is_active'            => true,
            ]);

            return $this->success($coupon, 'Coupon created', 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Show coupon
     *
     * Get details for a specific seller coupon.
     *
     * @authenticated
     *
     * @urlParam id integer required Coupon ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Coupon details","data":{"id":1,"code":"SUMMER2026","type":"percent","value":"15.00","used":5,"usage_limit":100}}
     * @response 404 {"status":false,"message":"Coupon not found"}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $coupon = Coupon::where('seller_id', $seller->id)->find($id);
            if (!$coupon) {
                return $this->error('Coupon not found', 404);
            }

            return $this->success($coupon, 'Coupon details');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Update coupon
     *
     * Update an existing seller coupon.
     *
     * @authenticated
     *
     * @urlParam id integer required Coupon ID. Example: 1
     *
     * @bodyParam value number Discount value. Example: 20
     * @bodyParam max_discount_amount number Max discount cap for percent coupons. Example: 50.00
     * @bodyParam description string Description. Example: Updated summer sale
     * @bodyParam min_order_amount number Minimum order amount. Example: 15.00
     * @bodyParam usage_limit integer Total usage limit. Example: 200
     * @bodyParam expires_at date Expiry date. Example: 2026-12-31
     * @bodyParam is_active boolean Enable/disable. Example: true
     * @bodyParam include_products array Product IDs (must be your products). Example: [5, 10]
     *
     * @response 200 {"status":true,"message":"Coupon updated","data":{}}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $coupon = Coupon::where('seller_id', $seller->id)->find($id);
            if (!$coupon) {
                return $this->error('Coupon not found', 404);
            }

            $request->validate([
                'type'                 => 'sometimes|in:fixed,percent',
                'value'                => 'sometimes|numeric|min:0.01',
                'max_discount_amount'  => 'nullable|numeric|min:0.01',
                'description'          => 'nullable|string|max:255',
                'min_order_amount'     => 'nullable|numeric|min:0',
                'max_order_amount'     => 'nullable|numeric|min:0',
                'usage_limit'          => 'nullable|integer|min:1',
                'usage_limit_per_user' => 'nullable|integer|min:1',
                'starts_at'            => 'nullable|date',
                'expires_at'           => 'nullable|date',
                'include_products'     => 'nullable|array',
                'include_products.*'   => 'integer',
                'is_active'            => 'sometimes|boolean',
            ]);

            $type  = $request->input('type', $coupon->type);
            $value = $request->input('value', $coupon->value);
            if ($type === 'percent' && $value > 100) {
                return $this->error('Percent discount cannot exceed 100', 422);
            }

            if ($request->has('include_products') && !empty($request->include_products)) {
                $sellerProductIds = SellerOffer::where('seller_id', $seller->id)
                    ->where('status', 'active')
                    ->pluck('product_id')
                    ->unique()
                    ->toArray();

                $invalid = array_diff($request->include_products, $sellerProductIds);
                if (!empty($invalid)) {
                    return $this->error('Some product IDs do not belong to your active offers: ' . implode(', ', $invalid), 422);
                }
            }

            $coupon->update($request->only([
                'type', 'value', 'max_discount_amount', 'description',
                'min_order_amount', 'max_order_amount',
                'usage_limit', 'usage_limit_per_user', 'starts_at', 'expires_at',
                'include_products', 'is_active',
            ]));

            return $this->success($coupon->fresh(), 'Coupon updated');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Delete coupon
     *
     * Delete a seller coupon. Only coupons with zero usage can be deleted.
     *
     * @authenticated
     *
     * @urlParam id integer required Coupon ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Coupon deleted"}
     * @response 422 {"status":false,"message":"Cannot delete a coupon that has been used"}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $coupon = Coupon::where('seller_id', $seller->id)->find($id);
            if (!$coupon) {
                return $this->error('Coupon not found', 404);
            }

            if ($coupon->used > 0) {
                return $this->error('Cannot delete a coupon that has been used. Deactivate it instead.', 422);
            }

            $coupon->delete();

            return $this->success(null, 'Coupon deleted');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }
}
