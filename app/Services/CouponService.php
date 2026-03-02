<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\SellerOffer;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * Validate a coupon against the given cart context and return the result.
     *
     * @param  string      $code     Raw coupon code (any case)
     * @param  float       $subtotal Full cart subtotal
     * @param  array       $items    Cart items — each must have: product_id, seller_offer_id, quantity, unit_price, line_total, and optionally category_ids[]
     * @param  int|null    $userId
     * @return array{valid: bool, error?: string, coupon?: Coupon, discount?: float, applicable_subtotal?: float, coupon_data?: array}
     */
    public function validate(string $code, float $subtotal, array $items, ?int $userId = null): array
    {
        $coupon = Coupon::where('code', strtoupper($code))->lockForUpdate()->first();

        if (!$coupon) {
            $coupon = Coupon::whereRaw('UPPER(code) = ?', [strtoupper($code)])->lockForUpdate()->first();
        }

        if (!$coupon || !$coupon->isActive()) {
            return self::fail('Invalid or expired coupon code.');
        }

        if ($coupon->usage_limit && $coupon->used >= $coupon->usage_limit) {
            return self::fail('Coupon usage limit reached.');
        }

        if ($coupon->usage_limit_per_user && $userId) {
            $userUsageCount = Order::where('user_id', $userId)
                ->whereRaw("JSON_EXTRACT(meta, '$.coupon.coupon_id') = ?", [$coupon->id])
                ->whereNotIn('status', ['cancelled'])
                ->count();

            if ($userUsageCount >= $coupon->usage_limit_per_user) {
                return self::fail('You have reached the usage limit for this coupon.');
            }
        }

        $applicableItems = $this->resolveApplicableItems($coupon, $items);

        if (empty($applicableItems)) {
            if ($coupon->seller_id) {
                return self::fail('This coupon only applies to products from this seller.');
            }
            return self::fail('Coupon not applicable for selected products.');
        }

        $applicableSubtotal = array_sum(array_column($applicableItems, 'line_total'));

        if ($coupon->min_order_amount && $applicableSubtotal < $coupon->min_order_amount) {
            return self::fail("Minimum order amount for this coupon is {$coupon->min_order_amount}.");
        }

        if ($coupon->max_order_amount && $applicableSubtotal > $coupon->max_order_amount) {
            return self::fail("Maximum order amount for this coupon is {$coupon->max_order_amount}.");
        }

        $discount = $this->calculateDiscount($coupon, $applicableSubtotal);

        return [
            'valid'               => true,
            'coupon'              => $coupon,
            'discount'            => $discount,
            'applicable_subtotal' => $applicableSubtotal,
            'coupon_data'         => [
                'coupon_id'  => $coupon->id,
                'code'       => $coupon->code,
                'type'       => $coupon->type,
                'value'      => $coupon->value,
                'seller_id'  => $coupon->seller_id,
                'discount'   => $discount,
            ],
        ];
    }

    /**
     * Calculate discount for the given coupon and applicable subtotal,
     * respecting max_discount_amount cap.
     */
    public function calculateDiscount(Coupon $coupon, float $applicableSubtotal): float
    {
        if ($coupon->type === 'percent') {
            $discount = round(($coupon->value / 100) * $applicableSubtotal, 2);

            if ($coupon->max_discount_amount && $discount > (float) $coupon->max_discount_amount) {
                $discount = (float) $coupon->max_discount_amount;
            }
        } else {
            $discount = min((float) $coupon->value, $applicableSubtotal);
        }

        return $discount;
    }

    /**
     * Filter cart items to only those the coupon applies to.
     * Handles seller scoping, category restrictions, and product restrictions.
     */
    public function resolveApplicableItems(Coupon $coupon, array $items): array
    {
        $applicable = $items;

        if ($coupon->seller_id) {
            $sellerProductIds = SellerOffer::where('seller_id', $coupon->seller_id)
                ->where('status', 'active')
                ->pluck('product_id')
                ->unique()
                ->toArray();

            $applicable = array_filter($applicable, fn ($item) =>
                in_array($item['product_id'], $sellerProductIds)
            );
        }

        if (!empty($coupon->include_products)) {
            $applicable = array_filter($applicable, fn ($item) =>
                in_array($item['product_id'], $coupon->include_products)
            );
        }

        if (!empty($coupon->exclude_products)) {
            $applicable = array_filter($applicable, fn ($item) =>
                !in_array($item['product_id'], $coupon->exclude_products)
            );
        }

        $categoryIds = [];
        foreach ($applicable as $item) {
            if (!empty($item['category_ids'])) {
                $categoryIds = array_merge($categoryIds, $item['category_ids']);
            }
        }
        $categoryIds = array_unique($categoryIds);

        if (!empty($coupon->include_categories)) {
            if (empty(array_intersect($coupon->include_categories, $categoryIds))) {
                return [];
            }
        }

        if (!empty($coupon->exclude_categories)) {
            if (!empty(array_intersect($coupon->exclude_categories, $categoryIds))) {
                return [];
            }
        }

        return array_values($applicable);
    }

    public function incrementUsage(Coupon $coupon): void
    {
        $coupon->increment('used');
    }

    public function decrementUsage(int $couponId): void
    {
        Coupon::where('id', $couponId)
            ->where('used', '>', 0)
            ->decrement('used');
    }

    private static function fail(string $message): array
    {
        return ['valid' => false, 'error' => $message];
    }
}
