<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaxController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\TaxonomyController;
use App\Http\Controllers\Api\BlogCommentController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\BlogCategoryController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\ProductRequestController;
use App\Http\Controllers\Api\SellerWithdrawController;
use App\Http\Controllers\Api\ProductAttributeController;

Route::prefix('v1')->group(function () {



     /*
    |--------------------------------------------------------------------------
    | Payment Webhooks
    |--------------------------------------------------------------------------
    */
    Route::post('/webhooks/payment/{gateway}', [PaymentWebhookController::class, 'handle'])
    ->name('payment.webhook');
    

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {

        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [UserController::class, 'me']);
        });

    });

    /*
    |--------------------------------------------------------------------------
    | Currencies
    |--------------------------------------------------------------------------
    */
    Route::prefix('currencies')->group(function () {
        Route::get('/', [CurrencyController::class, 'index']);
        Route::get('/default', [CurrencyController::class, 'default']);
        Route::get('/convert', [CurrencyController::class, 'convert']);
        Route::get('/{code}', [CurrencyController::class, 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Product Attributes
    |--------------------------------------------------------------------------
    */
    Route::prefix('product-attributes')->group(function () {
        Route::get('/', [ProductAttributeController::class, 'index']);

        Route::get('/categories', [ProductAttributeController::class, 'categories']);
        Route::get('/platforms', [ProductAttributeController::class, 'platforms']);
        Route::get('/types', [ProductAttributeController::class, 'types']);
        Route::get('/regions', [ProductAttributeController::class, 'regions']);
        Route::get('/languages', [ProductAttributeController::class, 'languages']);
        Route::get('/works-on', [ProductAttributeController::class, 'worksOn']);
        Route::get('/developers', [ProductAttributeController::class, 'developers']);
        Route::get('/publishers', [ProductAttributeController::class, 'publishers']);
    });



    /*
    |--------------------------------------------------------------------------
    | Products (Public)
    |--------------------------------------------------------------------------
    */
    Route::prefix('products')->group(function () {

        // Live search (autocomplete)
        Route::get('/search', [ProductController::class, 'search']);

        // Product listing (filters, pagination, sorting)
        Route::get('/', [ProductController::class, 'index']);

        // Product details
        Route::get('/{id}', [ProductController::class, 'show'])
            ->whereNumber('id');

        // Related / similar products
        Route::get('/{id}/related', [ProductController::class, 'related'])
            ->whereNumber('id');

        // Recently viewed / trending products
        Route::get('/trending', [ProductController::class, 'trending']);
    });


    /*
    |--------------------------------------------------------------------------
    | Product Requests (Authenticated)
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/product-requests', [ProductRequestController::class, 'index']);
        Route::post('/product-requests', [ProductRequestController::class, 'store']);
        Route::get('/product-requests/{id}', [ProductRequestController::class, 'show']);
        Route::put('/product-requests/{id}', [ProductRequestController::class, 'update']);
        Route::delete('/product-requests/{id}', [ProductRequestController::class, 'destroy']);

    });



    /*
    |--------------------------------------------------------------------------
    | Product Review APIs
    |--------------------------------------------------------------------------
    */

    // Public – view approved reviews
    Route::get('/products/{product}/reviews', [ProductReviewController::class, 'index']);
    Route::get('/products/{product}/reviews/summary', [ProductReviewController::class, 'summary']);

    // Authenticated – create/update/delete review
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store']);
        Route::put('/reviews/{review}', [ProductReviewController::class, 'update']);
        Route::delete('/reviews/{review}', [ProductReviewController::class, 'destroy']);
    });

    

    /*
    |--------------------------------------------------------------------------
    | Orders (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::get('/my-orders', [OrderController::class, 'index']);
    });

    /*
    |--------------------------------------------------------------------------
    | Transactions (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    });


    /*
    |--------------------------------------------------------------------------
    | Sellers (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // Public / User-facing
        Route::get('/sellers', [SellerController::class, 'index']);
        Route::get('/sellers/{id}', [SellerController::class, 'show']);

        // Seller (Owner)
        Route::post('/seller', [SellerController::class, 'store']);
        Route::put('/seller/{id}', [SellerController::class, 'update']);

    });


    /*
    |--------------------------------------------------------------------------
    | Sellers Withdrawals (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // Seller withdraws
        Route::get('/seller/withdraws', [SellerWithdrawController::class, 'index']);
        Route::post('/seller/withdraws', [SellerWithdrawController::class, 'store']);
        Route::get('/seller/withdraws/{id}', [SellerWithdrawController::class, 'show']);

    });


    /*
    |--------------------------------------------------------------------------
    | Coupons (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // Public / User-facing
        Route::get('/coupons', [CouponController::class, 'index']);
        Route::get('/coupons/{id}', [CouponController::class, 'show']);

    });

    // Coupon validation (checkout)
    Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);



    /*
    |--------------------------------------------------------------------------
    | Taxes
    |--------------------------------------------------------------------------
    */
    Route::get('/taxes', [TaxController::class, 'index']);
    Route::post('/taxes/calculate', [TaxController::class, 'calculate']);
    Route::get('/seller/taxes', [TaxController::class, 'sellerTaxes']);


    
    /*
    |--------------------------------------------------------------------------
    | Sliders
    |--------------------------------------------------------------------------
    */

    // Public (Frontend / Homepage)
    Route::get('/sliders', [SliderController::class, 'index']);
    Route::get('/sliders/{id}', [SliderController::class, 'show']);


    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    */

    Route::get('/pages', [PageController::class, 'index']);
    Route::get('/pages/{page:slug}', [PageController::class, 'show']);



    /*
    |--------------------------------------------------------------------------
    | Blogs
    |--------------------------------------------------------------------------
    */
    Route::get('/blogs', [BlogController::class, 'index']);
    Route::get('/blogs/{slug}', [BlogController::class, 'show']);



    /*
    |--------------------------------------------------------------------------
    | Blog Categories
    |--------------------------------------------------------------------------
    */
    Route::get('/blog-categories', [BlogCategoryController::class, 'index']);
    Route::get('/blog-categories/{slug}', [BlogCategoryController::class, 'show']);



    /*
    |--------------------------------------------------------------------------
    | Blog Comments
    |--------------------------------------------------------------------------
    */
    Route::get('/blogs/{blog}/comments', [BlogCommentController::class, 'index']);
    Route::middleware('auth:sanctum')->post('/blogs/{blog}/comments', [BlogCommentController::class, 'store']);




    /*
    |--------------------------------------------------------------------------
    | Wallet & Transactions
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // Wallet
        Route::get('/wallet', [WalletController::class, 'show']);
        Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
        Route::get('/wallet/settings', [WalletController::class, 'settings']);

    });




    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    */
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::get('/payment-methods/{code}', [PaymentMethodController::class, 'show']);




    /*
    |--------------------------------------------------------------------------
    | User Profile
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // User profile
        Route::get('/profile', [UserProfileController::class, 'show']);
        Route::post('/profile', [UserProfileController::class, 'storeOrUpdate']);

    });











    

});
