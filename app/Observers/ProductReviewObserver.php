<?php

namespace App\Observers;

use App\Models\ProductReview;
use App\Models\Seller;

class ProductReviewObserver
{
    public function updated(ProductReview $review): void
    {
        if ($review->wasChanged('status')) {
            Seller::recalculateRatingsForProduct($review->product_id);
        }
    }

    public function deleted(ProductReview $review): void
    {
        Seller::recalculateRatingsForProduct($review->product_id);
    }
}
