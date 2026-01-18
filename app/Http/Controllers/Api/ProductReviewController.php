<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    /**
     * List product reviews
     *
     * Retrieve all approved reviews for a specific product.
     *
     * @group Product Reviews
     *
     * @urlParam product integer required The ID of the product. Example: 12
     *
     * @queryParam page integer The page number for pagination. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "rating": 5,
     *       "title": "Excellent product",
     *       "review": "Highly recommended",
     *       "created_at": "2026-01-18T10:30:00Z",
     *       "user": {
     *         "id": 3,
     *         "name": "John Doe"
     *       }
     *     }
     *   ]
     * }
     */
    public function index(Product $product)
    {
        $reviews = ProductReview::query()
            ->with('user:id,name')
            ->where('product_id', $product->id)
            ->where('status', 'approved')
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }

    /**
     * Product review summary
     *
     * Retrieve average rating and total number of approved reviews for a product.
     *
     * @group Product Reviews
     *
     * @urlParam product integer required The ID of the product. Example: 12
     *
     * @response 200 {
     *   "average_rating": 4.6,
     *   "total_reviews": 128
     * }
     */
    public function summary(Product $product)
    {
        $query = ProductReview::where('product_id', $product->id)
            ->where('status', 'approved');

        return response()->json([
            'average_rating' => round($query->avg('rating'), 1),
            'total_reviews'  => $query->count(),
        ]);
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
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title'  => 'nullable|string|max:255',
            'review' => 'nullable|string|max:2000',
        ]);

        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id'    => $request->user()->id,
            'rating'     => $validated['rating'],
            'title'      => $validated['title'] ?? null,
            'review'     => $validated['review'] ?? null,
            'status'     => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Your review has been submitted and is pending moderation.',
            'review'  => $review,
        ], 201);
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
        $this->authorize('update', $review);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title'  => 'nullable|string|max:255',
            'review' => 'nullable|string|max:2000',
        ]);

        $review->update([
            'rating' => $validated['rating'],
            'title'  => $validated['title'] ?? null,
            'review' => $validated['review'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Your review has been updated and is pending re-approval.',
            'review'  => $review,
        ]);
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

        $review->delete();

        return response()->json([
            'message' => 'The review has been deleted successfully.',
        ]);
    }
}
