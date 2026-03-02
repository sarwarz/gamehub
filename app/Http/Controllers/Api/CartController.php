<?php

namespace App\Http\Controllers\Api;

use App\Models\SellerOffer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

/**
 * @group Cart
 *
 * Cart validation endpoint. The cart lives entirely on the client (localStorage).
 * Before proceeding to checkout, validate that all items are still in stock
 * and prices haven't changed.
 */
class CartController extends Controller
{
    /**
     * Validate cart
     *
     * Validates an array of cart items, checking stock availability and
     * current pricing. Returns per-item status so the frontend can
     * highlight any problems before the user enters checkout.
     *
     * @unauthenticated
     *
     * @bodyParam items array required Cart items to validate. Example: [{"seller_offer_id":1,"quantity":2}]
     * @bodyParam items[].seller_offer_id integer required Seller offer ID. Example: 1
     * @bodyParam items[].quantity integer required Quantity. Example: 2
     * @bodyParam items[].expected_price number Optional. Price the customer saw. Example: 29.99
     *
     * @response 200 {"status":true,"message":"Cart validated","data":{"valid":true,"items":[{"seller_offer_id":1,"available":true,"in_stock":true,"available_stock":15,"current_price":"29.99","price_changed":false,"product":{"id":5,"title":"Windows 11 Pro","slug":"windows-11-pro","image":"uploads/products/win11.jpg"}}],"unavailable_count":0,"price_changed_count":0}}
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'items'                      => 'required|array|min:1|max:50',
            'items.*.seller_offer_id'    => 'required|integer',
            'items.*.quantity'           => 'required|integer|min:1',
            'items.*.expected_price'     => 'nullable|numeric|min:0',
        ]);

        try {
            $offerIds = collect($request->items)->pluck('seller_offer_id')->unique();

            $offers = SellerOffer::whereIn('id', $offerIds)
                ->with('product:id,title,slug,image')
                ->withCount(['keys as available_stock' => fn ($q) => $q->where('status', 'available')])
                ->get()
                ->keyBy('id');

            $result = [];
            $unavailableCount = 0;
            $priceChangedCount = 0;

            foreach ($request->items as $item) {
                $offer = $offers->get($item['seller_offer_id']);
                $qty = $item['quantity'];
                $expectedPrice = $item['expected_price'] ?? null;

                if (!$offer || $offer->status !== 'active') {
                    $result[] = [
                        'seller_offer_id' => $item['seller_offer_id'],
                        'available'       => false,
                        'in_stock'        => false,
                        'reason'          => 'Offer no longer available',
                    ];
                    $unavailableCount++;
                    continue;
                }

                $currentPrice = (float) $offer->retail_price;
                $inStock = $offer->available_stock >= $qty;
                $priceChanged = $expectedPrice !== null && abs($currentPrice - (float) $expectedPrice) > 0.01;

                if (!$inStock) $unavailableCount++;
                if ($priceChanged) $priceChangedCount++;

                $result[] = [
                    'seller_offer_id' => $offer->id,
                    'available'       => true,
                    'in_stock'        => $inStock,
                    'available_stock' => $offer->available_stock,
                    'current_price'   => number_format($currentPrice, 2, '.', ''),
                    'price_changed'   => $priceChanged,
                    'product'         => $offer->product?->only(['id', 'title', 'slug', 'image']),
                    'seller'          => [
                        'id'         => $offer->seller_id,
                        'store_name' => $offer->seller?->store_name,
                    ],
                ];
            }

            $allValid = $unavailableCount === 0 && $priceChangedCount === 0;

            return $this->success([
                'valid'               => $allValid,
                'items'               => $result,
                'unavailable_count'   => $unavailableCount,
                'price_changed_count' => $priceChangedCount,
            ], $allValid ? 'Cart validated' : 'Some items need attention');

        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to validate cart');
        }
    }
}
