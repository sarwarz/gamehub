<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Wishlist
 *
 * APIs for managing the user's product wishlist.
 * All endpoints require authentication.
 */
class WishlistController extends Controller
{
    /**
     * List wishlist
     *
     * Get the authenticated user's wishlist with product details
     * including best price from active seller offers.
     *
     * @authenticated
     *
     * @queryParam per_page integer Results per page (default 15). Example: 10
     *
     * @response 200 {"status":true,"message":"Wishlist fetched","data":{"current_page":1,"data":[{"id":1,"product":{"id":5,"title":"Windows 11 Pro","slug":"windows-11-pro","image":"uploads/products/win11.jpg","best_price":"29.99"},"added_at":"2026-02-28T10:00:00.000000Z"}],"total":3}}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = min((int) $request->input('per_page', 15), 50);

            $items = Wishlist::where('user_id', $request->user()->id)
                ->with(['product' => function ($q) {
                    $q->select('id', 'title', 'slug', 'image')
                      ->withMin(['offers as best_price' => fn ($o) => $o->where('status', 'active')], 'retail_price');
                }])
                ->latest()
                ->paginate($perPage);

            return $this->success($items, 'Wishlist fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch wishlist.', 500);
        }
    }

    /**
     * Add to wishlist
     *
     * Add a product to the authenticated user's wishlist.
     * If the product is already in the wishlist, no duplicate is created.
     *
     * @authenticated
     *
     * @bodyParam product_id integer required Product ID to add. Example: 5
     *
     * @response 201 {"status":true,"message":"Product added to wishlist","data":{"id":1,"product_id":5,"created_at":"2026-02-28T10:00:00.000000Z"}}
     * @response 200 {"status":true,"message":"Product already in wishlist","data":{"id":1,"product_id":5}}
     * @response 404 {"status":false,"message":"Product not found."}
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        try {
            $product = Product::find($request->product_id);
            if (!$product) {
                return $this->error('Product not found.', 404);
            }

            $existing = Wishlist::where('user_id', $request->user()->id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($existing) {
                return $this->success($existing, 'Product already in wishlist');
            }

            $item = Wishlist::create([
                'user_id'    => $request->user()->id,
                'product_id' => $request->product_id,
            ]);

            return $this->success($item, 'Product added to wishlist', 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to add product to wishlist.', 500);
        }
    }

    /**
     * Remove from wishlist
     *
     * Remove a product from the authenticated user's wishlist.
     *
     * @authenticated
     *
     * @urlParam productId integer required Product ID to remove. Example: 5
     *
     * @response 200 {"status":true,"message":"Product removed from wishlist"}
     * @response 404 {"status":false,"message":"Product not in wishlist."}
     */
    public function destroy($productId): JsonResponse
    {
        try {
            $deleted = Wishlist::where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->delete();

            if (!$deleted) {
                return $this->error('Product not in wishlist.', 404);
            }

            return $this->success(null, 'Product removed from wishlist');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to remove product from wishlist.', 500);
        }
    }

    /**
     * Check wishlist status
     *
     * Check whether a specific product is in the user's wishlist.
     *
     * @authenticated
     *
     * @urlParam productId integer required Product ID to check. Example: 5
     *
     * @response 200 {"status":true,"message":"Checked","data":{"in_wishlist":true}}
     */
    public function check($productId): JsonResponse
    {
        try {
            $exists = Wishlist::where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->exists();

            return $this->success(['in_wishlist' => $exists], 'Checked');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to check wishlist status.', 500);
        }
    }

    /**
     * Clear wishlist
     *
     * Remove all products from the authenticated user's wishlist.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"Wishlist cleared"}
     */
    public function clear(): JsonResponse
    {
        try {
            Wishlist::where('user_id', auth()->id())->delete();

            return $this->success(null, 'Wishlist cleared');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to clear wishlist.', 500);
        }
    }

    /**
     * Wishlist count
     *
     * Get the total number of items in the user's wishlist.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"Count fetched","data":{"count":5}}
     */
    public function count(): JsonResponse
    {
        try {
            $count = Wishlist::where('user_id', auth()->id())->count();

            return $this->success(['count' => $count], 'Count fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch wishlist count.', 500);
        }
    }
}
