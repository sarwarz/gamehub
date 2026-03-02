<?php

namespace App\Http\Controllers\Api;

use App\Models\Seller;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

/**
 * @group Seller Storefront
 *
 * Public review endpoints for a seller's storefront.
 * Shows reviews across all of the seller's products.
 */
class StoreReviewController extends Controller
{
    /**
     * Seller store reviews
     *
     * Get paginated reviews for all products sold by a given seller.
     * Reviews are filtered to only show approved ones.
     *
     * @unauthenticated
     *
     * @urlParam slug string required Seller store slug. Example: tech-store
     *
     * @queryParam rating integer Filter by rating (1-5). Example: 5
     * @queryParam sort string Sort: newest (default), oldest, highest, lowest. Example: newest
     * @queryParam per_page integer Results per page (default 10). Example: 20
     *
     * @response 200 {"status":true,"message":"Reviews fetched","data":{"reviews":{"current_page":1,"data":[{"id":1,"rating":5,"title":"Great!","review":"Fast delivery","user":"John D.","product":{"id":5,"title":"Windows 11","slug":"windows-11"},"seller_reply":null,"created_at":"2026-01-15T12:00:00Z"}],"total":25},"summary":{"total_reviews":25,"average_rating":4.3,"breakdown":{"5":10,"4":8,"3":4,"2":2,"1":1}}}}
     * @response 404 {"status":false,"message":"Store not found"}
     */
    public function index(Request $request, string $slug): JsonResponse
    {
        try {
            $seller = Seller::where('slug', $slug)->active()->first();
            if (!$seller) {
                return $this->error('Store not found', 404);
            }

            $productIds = $seller->offers()
                ->where('status', 'active')
                ->pluck('product_id')
                ->unique();

            $query = ProductReview::whereIn('product_id', $productIds)
                ->where('status', 'approved')
                ->with(['product:id,title,slug,image', 'user:id,name']);

            if ($request->filled('rating')) {
                $query->where('rating', $request->rating);
            }

            $query = match ($request->input('sort')) {
                'oldest'  => $query->oldest(),
                'highest' => $query->orderByDesc('rating'),
                'lowest'  => $query->orderBy('rating'),
                default   => $query->latest(),
            };

            $reviews = $query->paginate(min($request->integer('per_page', 10), 50));

            $reviews->getCollection()->transform(fn ($r) => [
                'id'           => $r->id,
                'rating'       => $r->rating,
                'title'        => $r->title,
                'review'       => $r->review,
                'user'         => $r->user?->name,
                'product'      => $r->product?->only(['id', 'title', 'slug']),
                'seller_reply' => $r->seller_reply,
                'created_at'   => $r->created_at?->toISOString(),
            ]);

            $approvedBase = ProductReview::whereIn('product_id', $productIds)
                ->where('status', 'approved');

            $total = $approvedBase->clone()->count();
            $avg = $total > 0 ? round($approvedBase->clone()->avg('rating'), 2) : 0;

            $breakdown = [];
            for ($i = 5; $i >= 1; $i--) {
                $breakdown[$i] = $approvedBase->clone()->where('rating', $i)->count();
            }

            return $this->success([
                'reviews' => $reviews,
                'summary' => [
                    'total_reviews'  => $total,
                    'average_rating' => $avg,
                    'breakdown'      => $breakdown,
                ],
            ], 'Reviews fetched');

        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }
}
