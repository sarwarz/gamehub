<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Seller Products
 *
 * APIs for sellers to view products in their portfolio
 * (products they have active offers on) with sales stats.
 */
class SellerProductController extends Controller
{
    /**
     * My products
     *
     * List products the seller has offers on, with sales stats and stock info.
     *
     * @authenticated
     *
     * @queryParam status string Filter offers by status: active, inactive. Example: active
     * @queryParam search string Search by product title. Example: Windows
     * @queryParam per_page integer Results per page (default 15). Example: 10
     *
     * @response 200 {"status":true,"message":"Products fetched","data":{"current_page":1,"data":[],"total":0}}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $offers = $seller->offers()
                ->with([
                    'product:id,title,slug,image,status',
                    'product.categories:id,name',
                ])
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->when($request->search, function ($q, $s) {
                    $q->whereHas('product', fn ($pq) => $pq->where('title', 'like', "%{$s}%"));
                })
                ->withCount([
                    'keys as total_keys',
                    'keys as available_keys' => fn ($q) => $q->where('status', 'available'),
                    'keys as sold_keys'      => fn ($q) => $q->where('status', 'sold'),
                ])
                ->latest()
                ->paginate(min($request->integer('per_page', 15), 50));

            $offers->getCollection()->transform(fn ($offer) => [
                'offer_id'       => $offer->id,
                'product'        => [
                    'id'         => $offer->product?->id,
                    'title'      => $offer->product?->title,
                    'slug'       => $offer->product?->slug,
                    'image'      => $offer->product?->image,
                    'status'     => $offer->product?->status,
                    'categories' => $offer->product?->categories?->pluck('name'),
                ],
                'retail_price'   => $offer->retail_price,
                'status'         => $offer->status,
                'stock'          => [
                    'total'      => $offer->total_keys,
                    'available'  => $offer->available_keys,
                    'sold'       => $offer->sold_keys,
                ],
                'sale_mode'      => $offer->sale_mode,
                'created_at'     => $offer->created_at?->toISOString(),
            ]);

            return $this->success($offers, 'Products fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Product stats
     *
     * Get sales statistics for a specific product the seller has an offer on.
     *
     * @authenticated
     *
     * @urlParam offerId integer required Seller offer ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Stats fetched","data":{}}
     * @response 404 {"status":false,"message":"Offer not found"}
     */
    public function stats(Request $request, int $offerId): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $offer = $seller->offers()
                ->with('product:id,title,slug,image')
                ->withCount([
                    'keys as total_keys',
                    'keys as available_keys' => fn ($q) => $q->where('status', 'available'),
                    'keys as sold_keys'      => fn ($q) => $q->where('status', 'sold'),
                ])
                ->find($offerId);

            if (!$offer) {
                return $this->error('Offer not found', 404);
            }

            $earnings = \App\Models\SellerEarning::where('seller_id', $seller->id)
                ->whereHas('orderItem', fn ($q) => $q->where('seller_offer_id', $offerId));

            return $this->success([
                'offer'   => [
                    'id'           => $offer->id,
                    'retail_price' => $offer->retail_price,
                    'status'       => $offer->status,
                    'product'      => $offer->product?->only(['id', 'title', 'slug', 'image']),
                ],
                'stock'   => [
                    'total'     => $offer->total_keys,
                    'available' => $offer->available_keys,
                    'sold'      => $offer->sold_keys,
                ],
                'revenue' => [
                    'gross'      => $earnings->clone()->sum('gross_amount'),
                    'commission' => $earnings->clone()->sum('commission'),
                    'net'        => $earnings->clone()->sum('net_amount'),
                    'orders'     => $earnings->clone()->distinct('order_id')->count('order_id'),
                ],
            ], 'Stats fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }
}
