<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Seller Reviews
 *
 * APIs for sellers to view and manage reviews on their products.
 * All endpoints require authentication and an active seller account.
 */
class SellerReviewController extends Controller
{
    /**
     * List reviews on my products
     *
     * Get all reviews for products the seller has active offers on.
     *
     * @authenticated
     *
     * @queryParam status string Filter by status: approved, pending, rejected. Example: approved
     * @queryParam rating integer Filter by rating (1-5). Example: 5
     * @queryParam product_id integer Filter by product. Example: 3
     * @queryParam per_page integer Results per page (default 15). Example: 10
     *
     * @response 200 {"status":true,"message":"Reviews fetched","data":{"current_page":1,"data":[],"total":0}}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $productIds = $seller->offers()->pluck('product_id')->unique();

            $reviews = ProductReview::whereIn('product_id', $productIds)
                ->with([
                    'product:id,title,slug,image',
                    'user:id,name',
                ])
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->when($request->rating, fn ($q, $r) => $q->where('rating', $r))
                ->when($request->product_id, fn ($q, $p) => $q->where('product_id', $p))
                ->latest()
                ->paginate(min($request->integer('per_page', 15), 50));

            return $this->success($reviews, 'Reviews fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Review summary
     *
     * Get aggregated review statistics for the seller's products.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"Summary fetched","data":{"total_reviews":50,"average_rating":"4.20","rating_breakdown":{"5":20,"4":15,"3":10,"2":3,"1":2},"recent_reviews":[]}}
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $productIds = $seller->offers()->pluck('product_id')->unique();

            $reviews = ProductReview::whereIn('product_id', $productIds)
                ->where('status', 'approved');

            $total = $reviews->clone()->count();
            $average = $total > 0 ? round($reviews->clone()->avg('rating'), 2) : 0;

            $breakdown = [];
            for ($i = 5; $i >= 1; $i--) {
                $breakdown[$i] = $reviews->clone()->where('rating', $i)->count();
            }

            $recent = $reviews->clone()
                ->with(['product:id,title,slug', 'user:id,name'])
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($r) => [
                    'id'      => $r->id,
                    'rating'  => $r->rating,
                    'title'   => $r->title,
                    'review'  => \Illuminate\Support\Str::limit($r->review, 100),
                    'product' => $r->product?->only(['id', 'title', 'slug']),
                    'user'    => $r->user?->name,
                    'date'    => $r->created_at?->toISOString(),
                ]);

            return $this->success([
                'total_reviews'    => $total,
                'average_rating'   => $average,
                'rating_breakdown' => $breakdown,
                'recent_reviews'   => $recent,
            ], 'Summary fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Reply to a review
     *
     * @authenticated
     */
    public function reply(Request $request, int $reviewId): JsonResponse
    {
        if (!(bool) Setting::get('review', 'seller_can_reply', false)) {
            return $this->error('Seller replies are currently disabled.', 403);
        }

        $request->validate([
            'reply' => 'required|string|max:2000',
        ]);

        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $productIds = $seller->offers()->pluck('product_id')->unique();

            $review = ProductReview::whereIn('product_id', $productIds)
                ->where('id', $reviewId)
                ->first();

            if (!$review) {
                return $this->error('Review not found', 404);
            }

            $review->update([
                'seller_reply'    => $request->reply,
                'seller_reply_at' => now(),
            ]);

            return $this->success($review, 'Reply submitted successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }
}
