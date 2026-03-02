<?php

namespace App\Http\Controllers\Api;

use App\Models\RecentlyViewedProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

/**
 * @group Recently Viewed
 *
 * Track and retrieve products the user has recently viewed.
 * Useful for "Continue browsing" sections on the frontend.
 *
 * All endpoints require authentication.
 */
class RecentlyViewedController extends Controller
{
    /**
     * List recently viewed
     *
     * Get the authenticated user's recently viewed products, newest first.
     * Limited to the last 20 products.
     *
     * @authenticated
     *
     * @queryParam limit integer Number of items to return (max 30, default 12). Example: 12
     *
     * @response 200 {"status":true,"message":"Recently viewed products","data":[{"product_id":5,"title":"Windows 11 Pro","slug":"windows-11-pro","image":"uploads/products/win11.jpg","lowest_price":"25.00","viewed_at":"2026-03-02T10:30:00Z"}]}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $limit = min($request->integer('limit', 12), 30);

            $items = RecentlyViewedProduct::where('user_id', $request->user()->id)
                ->with([
                    'product:id,title,slug,image,short_description',
                    'product.offers' => fn ($q) => $q->where('status', 'active')->select('id', 'product_id', 'retail_price'),
                ])
                ->orderByDesc('viewed_at')
                ->limit($limit)
                ->get()
                ->filter(fn ($item) => $item->product && $item->product->status === 'active')
                ->map(fn ($item) => [
                    'product_id'        => $item->product_id,
                    'title'             => $item->product->title,
                    'slug'              => $item->product->slug,
                    'image'             => $item->product->image,
                    'short_description' => $item->product->short_description,
                    'lowest_price'      => $item->product->offers->min('retail_price'),
                    'viewed_at'         => $item->viewed_at?->toISOString(),
                ])
                ->values();

            return $this->success($items, 'Recently viewed products');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Track product view
     *
     * Record that the user viewed a product. If already viewed,
     * the timestamp is updated to now.
     *
     * @authenticated
     *
     * @bodyParam product_id integer required Product ID. Example: 5
     *
     * @response 200 {"status":true,"message":"View recorded"}
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        try {
            RecentlyViewedProduct::updateOrCreate(
                [
                    'user_id'    => $request->user()->id,
                    'product_id' => $request->product_id,
                ],
                ['viewed_at' => now()]
            );

            $count = RecentlyViewedProduct::where('user_id', $request->user()->id)->count();
            if ($count > 50) {
                RecentlyViewedProduct::where('user_id', $request->user()->id)
                    ->orderBy('viewed_at')
                    ->limit($count - 50)
                    ->delete();
            }

            return $this->success(null, 'View recorded');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Clear history
     *
     * Remove all recently viewed products for the authenticated user.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"History cleared"}
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            RecentlyViewedProduct::where('user_id', $request->user()->id)->delete();

            return $this->success(null, 'History cleared');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }
}
