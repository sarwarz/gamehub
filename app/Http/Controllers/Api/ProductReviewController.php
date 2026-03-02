<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ProductReview;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    /**
     * List product reviews
     *
     * Retrieve all approved reviews for a specific product. Public, no auth required.
     *
     * @group Product Reviews
     * @unauthenticated
     *
     * @urlParam product integer required The ID of the product. Example: 12
     * @queryParam page integer Page number for pagination. Example: 1
     * @queryParam per_page integer Items per page (default 10). Example: 10
     *
     * @response 200 {"data":[{"id":1,"rating":5,"title":"Excellent product","review":"Highly recommended","created_at":"2026-01-18T10:30:00Z","user":{"id":3,"name":"John Doe"}}]}
     */
    public function index(Product $product)
    {
        try {
            $reviews = ProductReview::query()
                ->with('user:id,name')
                ->where('product_id', $product->id)
                ->where('status', 'approved')
                ->latest()
                ->paginate(10);

            return $this->success($reviews, 'Reviews fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch reviews');
        }
    }

    /**
     * Product review summary
     *
     * Average rating and total count of approved reviews for a product. Public, no auth required.
     *
     * @group Product Reviews
     * @unauthenticated
     *
     * @urlParam product integer required The ID of the product. Example: 12
     *
     * @response 200 {"average_rating":4.6,"total_reviews":128}
     */
    public function summary(Product $product)
    {
        try {
            $query = ProductReview::where('product_id', $product->id)
                ->where('status', 'approved');

            return $this->success([
                'average_rating' => round($query->avg('rating'), 1),
                'total_reviews'  => $query->count(),
            ], 'Review summary');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch review summary');
        }
    }

    /**
     * Submit a product review
     *
     * Submit a new review for a product. Authentication required.
     * The review will be stored with `pending` status until moderation.
     *
     * @group Product Reviews
     * @authenticated
     *
     * @urlParam product integer required The ID of the product. Example: 12
     *
     * @bodyParam rating integer required Rating from 1 to 5. Example: 5
     * @bodyParam title string Optional review title. Example: Excellent product
     * @bodyParam review string Optional review message. Example: Worth every penny.
     *
     * @response 201 {
     *   "message": "Your review has been submitted and is pending moderation."
     * }
     */
    public function store(Request $request, Product $product)
    {
        $reviewSettings = Setting::group('review');

        if (isset($reviewSettings['reviews_enabled']) && !$reviewSettings['reviews_enabled']) {
            return $this->error('Reviews are currently disabled.', 403);
        }

        if (!empty($reviewSettings['require_purchase_for_review'])) {
            $hasPurchased = OrderItem::whereHas('order', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id)
                  ->where('payment_status', 'paid');
            })->where('product_id', $product->id)->exists();

            if (!$hasPurchased) {
                return $this->error('You must purchase this product before leaving a review.', 403);
            }
        }

        $minLen = (int) ($reviewSettings['min_review_length'] ?? 0);
        $maxLen = (int) ($reviewSettings['max_review_length'] ?? 2000);
        $reviewRule = $minLen > 0
            ? "required|string|min:{$minLen}|max:{$maxLen}"
            : "nullable|string|max:{$maxLen}";

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title'  => 'nullable|string|max:255',
            'review' => $reviewRule,
        ]);

        if (ProductReview::where('user_id', $request->user()->id)->where('product_id', $product->id)->exists()) {
            return $this->error('You have already reviewed this product.', 422);
        }

        try {
            $moderationEnabled = isset($reviewSettings['review_moderation_enabled'])
                ? !empty($reviewSettings['review_moderation_enabled'])
                : true;
            $autoApprove = !$moderationEnabled || !empty($reviewSettings['auto_approve_reviews']);
            $status = $autoApprove ? 'approved' : 'pending';

            $review = ProductReview::create([
                'product_id' => $product->id,
                'user_id'    => $request->user()->id,
                'rating'     => $validated['rating'],
                'title'      => $validated['title'] ?? null,
                'review'     => $validated['review'] ?? null,
                'status'     => $status,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            try {
                if (Setting::get('notifications', 'new_product_review', true)) {
                    $review->loadMissing(['product.offers.seller.user']);
                    $sellerUsers = $review->product->offers
                        ->pluck('seller.user')
                        ->filter()
                        ->unique('id');
                    $sellerUsers->each(fn($u) => $u->notify(new \App\Notifications\NewProductReviewNotification($review)));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Product review notification failed: ' . $e->getMessage());
            }

            $message = $autoApprove
                ? 'Review submitted successfully.'
                : 'Your review has been submitted and is pending moderation.';

            return $this->success($review, $message, 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to submit review');
        }
    }

    /**
     * Update a review
     *
     * Update an existing product review owned by the authenticated user.
     * The review will be set back to `pending` for re-approval.
     *
     * @group Product Reviews
     * @authenticated
     *
     * @urlParam review integer required The ID of the review. Example: 5
     *
     * @bodyParam rating integer required Rating from 1 to 5. Example: 4
     * @bodyParam title string Optional review title. Example: Good value
     * @bodyParam review string Optional review message. Example: Improved after update.
     *
     * @response 200 {
     *   "message": "Your review has been updated and is pending re-approval."
     * }
     */
    public function update(Request $request, ProductReview $review)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title'  => 'nullable|string|max:255',
            'review' => 'nullable|string|max:2000',
        ]);

        $this->authorize('update', $review);

        try {
            $review->update([
                'rating' => $validated['rating'],
                'title'  => $validated['title'] ?? null,
                'review' => $validated['review'] ?? null,
                'status' => 'pending',
            ]);

            return $this->success($review, 'Review updated');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to update review');
        }
    }

    /**
     * Delete a review
     *
     * Delete a product review owned by the authenticated user.
     *
     * @group Product Reviews
     * @authenticated
     *
     * @urlParam review integer required The ID of the review. Example: 5
     *
     * @response 200 {
     *   "message": "The review has been deleted successfully."
     * }
     */
    public function destroy(ProductReview $review)
    {
        $this->authorize('delete', $review);

        try {
            $review->delete();

            return $this->success(null, 'Review deleted');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to delete review');
        }
    }
}
