<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\SellerOffer;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Seller Storefront
 *
 * Public APIs for viewing a seller's storefront page,
 * including their products, ratings, and store info.
 */
class SellerStoreController extends Controller
{
    /**
     * Seller storefront
     *
     * Get the public storefront for a seller by slug. Includes store info,
     * product listings with best prices, review summary, and total sales.
     *
     * @unauthenticated
     *
     * @urlParam slug string required Seller store slug. Example: tech-store
     *
     * @queryParam sort string Sort products: price_asc, price_desc, newest, popular. Example: price_asc
     * @queryParam category_id integer Filter by product category. Example: 1
     * @queryParam per_page integer Products per page (default 12). Example: 20
     *
     * @response 200 {"status":true,"message":"Store loaded","data":{"store":{...},"products":{...},"review_summary":{...}}}
     * @response 404 {"status":false,"message":"Store not found"}
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        try {
            $seller = Seller::where('slug', $slug)->active()->first();

            if (!$seller) {
                return $this->error('Store not found', 404);
            }

            $store = $seller->only([
                'id', 'store_name', 'slug', 'logo', 'banner', 'description',
                'country', 'rating', 'total_sales', 'is_verified', 'created_at',
            ]);

            $offersQuery = SellerOffer::where('seller_id', $seller->id)
                ->where('status', 'active')
                ->with([
                    'product:id,title,slug,image,short_description',
                    'product.categories:id,name,slug',
                ])
                ->withCount(['keys as available_stock' => fn ($q) => $q->where('status', 'available')]);

            if ($request->category_id) {
                $offersQuery->whereHas('product.categories', fn ($q) => $q->where('product_categories.id', $request->category_id));
            }

            $offersQuery = match ($request->input('sort')) {
                'price_asc'  => $offersQuery->orderBy('retail_price', 'asc'),
                'price_desc' => $offersQuery->orderBy('retail_price', 'desc'),
                'popular'    => $offersQuery->orderByDesc(
                    \Illuminate\Support\Facades\DB::raw('(SELECT COUNT(*) FROM order_items WHERE order_items.seller_offer_id = seller_offers.id)')
                ),
                default      => $offersQuery->latest(),
            };

            $products = $offersQuery->paginate($request->input('per_page', 12));

            $products->getCollection()->transform(fn ($offer) => [
                'offer_id'        => $offer->id,
                'price'           => $offer->retail_price,
                'available_stock' => $offer->available_stock,
                'product'         => [
                    'id'                => $offer->product?->id,
                    'title'             => $offer->product?->title,
                    'slug'              => $offer->product?->slug,
                    'image'             => $offer->product?->image,
                    'short_description' => $offer->product?->short_description,
                    'categories'        => $offer->product?->categories?->map(fn ($c) => $c->only(['id', 'name', 'slug'])),
                ],
            ]);

            $productIds = $seller->offers()->where('status', 'active')->pluck('product_id')->unique();
            $reviewCount = ProductReview::whereIn('product_id', $productIds)->where('status', 'approved')->count();
            $avgRating = $reviewCount > 0
                ? round(ProductReview::whereIn('product_id', $productIds)->where('status', 'approved')->avg('rating'), 2)
                : 0;

            return $this->success([
                'store'          => $store,
                'products'       => $products,
                'review_summary' => [
                    'total_reviews'  => $reviewCount,
                    'average_rating' => $avgRating,
                ],
            ], 'Store loaded');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }
}
