<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\SellerOffer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

/**
 * @group Product Offers
 *
 * Compare seller offers for a specific product.
 * Buyers use this to see all available sellers, prices, stock, and ratings
 * before adding to cart.
 *
 * @unauthenticated
 */
class ProductOfferController extends Controller
{
    /**
     * List offers for a product
     *
     * Returns all active seller offers for a product, sorted by price.
     * Each offer includes seller info, stock count, and pricing tiers.
     *
     * @urlParam productId integer required Product ID. Example: 5
     *
     * @queryParam sort string Sort: price_asc (default), price_desc, rating, newest. Example: price_asc
     * @queryParam region_id integer Filter by region ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Offers fetched","data":{"product":{"id":5,"title":"Windows 11 Pro","slug":"windows-11-pro","image":"..."},"offers":[{"id":1,"seller":{"id":1,"store_name":"GameHub","slug":"gamehub","rating":4.5,"is_verified":true},"retail_price":"29.99","available_stock":15,"edition":"Standard","region":"Global","platform":"PC"}],"total_offers":3,"lowest_price":"25.00"}}
     * @response 404 {"status":false,"message":"Product not found"}
     */
    public function index(Request $request, int $productId): JsonResponse
    {
        try {
            $product = Product::where('status', 'active')->find($productId);
            if (!$product) {
                return $this->error('Product not found', 404);
            }

            $query = SellerOffer::where('product_id', $productId)
                ->where('status', 'active')
                ->with([
                    'seller:id,store_name,slug,rating,is_verified,total_sales',
                ])
                ->withCount(['keys as available_stock' => fn ($q) => $q->where('status', 'available')]);

            if ($request->filled('region_id')) {
                $query->where('region_id', $request->region_id);
            }

            $query = match ($request->input('sort')) {
                'price_desc' => $query->orderByDesc('retail_price'),
                'rating'     => $query->orderByDesc(
                    \Illuminate\Support\Facades\DB::raw('(SELECT rating FROM sellers WHERE sellers.id = seller_offers.seller_id)')
                ),
                'newest'     => $query->latest(),
                default      => $query->orderBy('retail_price', 'asc'),
            };

            $offers = $query->get()->map(fn ($offer) => [
                'id'              => $offer->id,
                'seller'          => $offer->seller?->only(['id', 'store_name', 'slug', 'rating', 'is_verified', 'total_sales']),
                'retail_price'    => $offer->retail_price,
                'available_stock' => $offer->available_stock,
                'edition'         => $offer->edition,
                'region'          => $offer->region?->name ?? null,
                'platform'        => $offer->platform?->name ?? null,
                'sale_mode'       => $offer->sale_mode,
                'has_wholesale'   => in_array($offer->sale_mode, ['wholesale', 'both']),
                'wholesale_10_99_price'    => $offer->wholesale_10_99_price,
                'wholesale_100_plus_price' => $offer->wholesale_100_plus_price,
            ]);

            $lowestPrice = $offers->min('retail_price');

            return $this->success([
                'product'      => $product->only(['id', 'title', 'slug', 'image', 'short_description']),
                'offers'       => $offers,
                'total_offers' => $offers->count(),
                'lowest_price' => $lowestPrice,
            ], 'Offers fetched');

        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch offers');
        }
    }
}
