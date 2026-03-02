<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
     * Retrieve all active global (non-seller) coupons.
     *
     * @queryParam type string Optional. percent or fixed. Example: percent
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Coupons fetched successfully",
     *   "data": []
     * }
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $coupons = Coupon::where('is_active', true)
                ->whereNull('seller_id')
                ->when($request->type, fn ($q) => $q->where('type', $request->type))
                ->latest()
                ->paginate(10);

            $coupons->getCollection()->transform(fn ($coupon) => [
                'id'                  => $coupon->id,
                'code_hint'           => substr($coupon->code, 0, 3) . '***',
                'type'                => $coupon->type,
                'value'               => $coupon->value,
                'max_discount_amount' => $coupon->max_discount_amount,
                'min_order_amount'    => $coupon->min_order_amount,
                'max_order_amount'    => $coupon->max_order_amount,
                'starts_at'           => $coupon->starts_at,
                'expires_at'          => $coupon->expires_at,
                'description'         => $coupon->description,
            ]);

            return $this->success($coupons, 'Coupons fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch coupons.', 500);
        }
    }

    /**
     * Get coupon details
     *
     * Retrieve a single global coupon by ID.
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
    public function show($id): JsonResponse
    {
        try {
            $coupon = Coupon::where('is_active', true)->whereNull('seller_id')->find($id);

            if (!$coupon) {
                return $this->error('Coupon not found', 404);
            }

            return $this->success([
                'id'                  => $coupon->id,
                'code'                => $coupon->code,
                'type'                => $coupon->type,
                'value'               => $coupon->value,
                'max_discount_amount' => $coupon->max_discount_amount,
                'min_order_amount'    => $coupon->min_order_amount,
                'max_order_amount'    => $coupon->max_order_amount,
                'starts_at'           => $coupon->starts_at,
                'expires_at'          => $coupon->expires_at,
                'description'         => $coupon->description,
            ], 'Coupon details fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch coupon details.', 500);
        }
    }

    /**
     * Validate coupon
     *
     * Validate a coupon code against cart items. Handles seller-scoped coupons
     * automatically — if the coupon belongs to a seller, only that seller's
     * products count toward the discount.
     *
     * @authenticated
     *
     * @bodyParam code string required Coupon code. Example: SAVE20
     * @bodyParam items array required Cart items for seller-scope validation.
     * @bodyParam items[].product_id integer required Product ID. Example: 5
     * @bodyParam items[].seller_offer_id integer required Seller offer ID. Example: 12
     * @bodyParam items[].quantity integer required Quantity. Example: 1
     * @bodyParam items[].unit_price number required Unit price. Example: 29.99
     * @bodyParam items[].line_total number required Line total. Example: 29.99
     * @bodyParam items[].category_ids array Optional category IDs for the product. Example: [1,2]
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Coupon is valid",
     *   "data": {
     *     "discount": 6.00,
     *     "type": "percent",
     *     "value": 20,
     *     "applicable_subtotal": 29.99
     *   }
     * }
     */
    public function validateCoupon(Request $request, CouponService $couponService): JsonResponse
    {
        $data = $request->validate([
            'code'                    => 'required|string',
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|integer',
            'items.*.seller_offer_id' => 'required|integer',
            'items.*.quantity'        => 'required|integer|min:1',
            'items.*.unit_price'      => 'required|numeric|min:0',
            'items.*.line_total'      => 'required|numeric|min:0',
            'items.*.category_ids'    => 'nullable|array',
        ]);

        try {
            $subtotal = array_sum(array_column($data['items'], 'line_total'));

            $result = $couponService->validate(
                $data['code'],
                $subtotal,
                $data['items'],
                auth()->id(),
            );

            if (!$result['valid']) {
                return $this->error($result['error'], 422);
            }

            return $this->success([
                'discount'            => $result['discount'],
                'type'                => $result['coupon']->type,
                'value'               => $result['coupon']->value,
                'max_discount_amount' => $result['coupon']->max_discount_amount,
                'applicable_subtotal' => $result['applicable_subtotal'],
                'seller_id'           => $result['coupon']->seller_id,
            ], 'Coupon is valid');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to validate coupon.', 500);
        }
    }
}
