<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaxController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\TaxonomyController;
use App\Http\Controllers\Api\WholesaleController;
use App\Http\Controllers\Api\PriceAlertController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\BlogCommentController;
use App\Http\Controllers\Api\InvoiceApiController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\StoreReviewController;
use App\Http\Controllers\Api\BlogCategoryController;
use App\Http\Controllers\Api\ProductOfferController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\SellerCouponController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\ProductRequestController;
use App\Http\Controllers\Api\RecentlyViewedController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\SellerOfferController;
use App\Http\Controllers\Api\SellerWithdrawController;
use App\Http\Controllers\Api\ProductAttributeController;
use App\Http\Controllers\Api\SubscriberController as ApiSubscriberController;
use App\Http\Controllers\Api\ContactMessageController as ApiContactMessageController;
use App\Http\Controllers\Api\SupportTicketController as ApiSupportTicketController;
use App\Http\Controllers\Api\SellerTicketController;
use App\Http\Controllers\Api\TicketDepartmentController as ApiTicketDepartmentController;
use App\Http\Controllers\Api\SellerApplicationController;
use App\Http\Controllers\Api\WebsiteController as ApiWebsiteController;
use App\Http\Controllers\Api\MenuApiController;
use App\Http\Controllers\Api\FaqApiController;
use App\Http\Controllers\Api\StaticPageApiController;
use App\Http\Controllers\Api\NotificationController as ApiNotificationController;
use App\Http\Controllers\Api\OrderKeyController;
use App\Http\Controllers\Api\UserDashboardController;
use App\Http\Controllers\Api\SellerReviewController;
use App\Http\Controllers\Api\SellerProductController;
use App\Http\Controllers\Api\SellerStoreController;
use App\Http\Controllers\Api\RefundRequestController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SellerAnalyticsController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\AffiliateController as ApiAffiliateController;
use App\Http\Controllers\Api\AffiliateTrackingController;

Route::prefix('v1')->middleware('maintenance')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Payment Webhooks
    |--------------------------------------------------------------------------
    */
    Route::post('/webhooks/payment/{gateway}', [PaymentWebhookController::class, 'handle'])
        ->middleware('throttle:60,1')
        ->name('payment.webhook');

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {

        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
        Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware(['signed'])
            ->name('api.verification.verify');

        // Social login (Google, Facebook)
        Route::post('/social/{provider}', [SocialAuthController::class, 'handle'])->middleware('throttle:10,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [UserController::class, 'me']);
            Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:5,5');
            Route::post('/delete-account', [AuthController::class, 'deleteAccount'])->middleware('throttle:3,5');
            Route::post('/send-verification', [AuthController::class, 'sendVerificationEmail'])->middleware('throttle:3,5');
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
        Route::get('/search', [ProductController::class, 'search']);
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/slug/{slug}', [ProductController::class, 'showBySlug']);
        Route::get('/trending', [ProductController::class, 'trending']);
        Route::get('/{id}', [ProductController::class, 'show'])->whereNumber('id');
        Route::get('/{id}/related', [ProductController::class, 'related'])->whereNumber('id');
        Route::get('/{id}/offers', [ProductOfferController::class, 'index'])->whereNumber('id');
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
    Route::get('/products/{product}/reviews', [ProductReviewController::class, 'index']);
    Route::get('/products/{product}/reviews/summary', [ProductReviewController::class, 'summary']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store']);
        Route::put('/reviews/{review}', [ProductReviewController::class, 'update']);
        Route::delete('/reviews/{review}', [ProductReviewController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Cart Validation (Public)
    |--------------------------------------------------------------------------
    */
    Route::post('/cart/validate', [CartController::class, 'validate'])->middleware('throttle:30,1');

    /*
    |--------------------------------------------------------------------------
    | Checkout (Authenticated) — Payment-first checkout flow
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'throttle:30,1'])->prefix('checkout/sessions')->group(function () {
        Route::post('/', [CheckoutController::class, 'createSession']);
        Route::post('/{uuid}/pay', [CheckoutController::class, 'pay']);
        Route::get('/{uuid}/result', [CheckoutController::class, 'result']);
    });

    /*
    |--------------------------------------------------------------------------
    | Orders (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/my-orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::get('/orders/{order}/track', [OrderController::class, 'track']);
        Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice']);
        Route::get('/orders/{order}/invoice/download', [InvoiceApiController::class, 'download']);
        Route::get('/orders/{order}/reorder', [OrderController::class, 'reorder']);
    });

    /*
    |--------------------------------------------------------------------------
    | Seller Orders & Earnings (Authenticated Seller)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'api.role:seller'])->group(function () {
        Route::get('/seller/orders', [OrderController::class, 'sellerOrders']);
        Route::get('/seller/orders/{order}', [OrderController::class, 'sellerOrderShow']);
        Route::get('/seller/earnings', [OrderController::class, 'sellerEarnings']);
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
    | Sellers (Public)
    |--------------------------------------------------------------------------
    */
    Route::get('/sellers', [SellerController::class, 'index']);
    Route::get('/sellers/{id}', [SellerController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Seller Application (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->prefix('seller-application')->group(function () {
        Route::post('/', [SellerApplicationController::class, 'apply']);
        Route::get('/status', [SellerApplicationController::class, 'status']);
        Route::put('/', [SellerApplicationController::class, 'update']);
    });

    /*
    |--------------------------------------------------------------------------
    | Seller Account & Management (Authenticated Seller)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'api.role:seller', 'throttle:60,1'])->prefix('seller')->group(function () {

        // Store management
        Route::post('/', [SellerController::class, 'store']);
        Route::put('/{id}', [SellerController::class, 'update']);
        Route::get('/profile', [SellerController::class, 'profile']);
        Route::get('/dashboard', [SellerController::class, 'dashboard']);
        Route::get('/balance', [SellerController::class, 'balance']);

        // Offer management
        Route::get('/offers', [SellerOfferController::class, 'index']);
        Route::post('/offers', [SellerOfferController::class, 'store']);
        Route::get('/offers/{id}', [SellerOfferController::class, 'show']);
        Route::put('/offers/{id}', [SellerOfferController::class, 'update']);
        Route::delete('/offers/{id}', [SellerOfferController::class, 'destroy']);
        Route::get('/offers/{id}/keys', [SellerOfferController::class, 'keys']);
        Route::post('/offers/{id}/keys', [SellerOfferController::class, 'uploadKeys'])->middleware('throttle:10,1');
        Route::delete('/offers/{offerId}/keys/{keyId}', [SellerOfferController::class, 'deleteKey']);

        // Withdrawals
        Route::get('/withdraws', [SellerWithdrawController::class, 'index']);
        Route::get('/withdraws/methods', [SellerWithdrawController::class, 'methods']);
        Route::post('/withdraws', [SellerWithdrawController::class, 'store'])->middleware('throttle:5,1');
        Route::get('/withdraws/{id}', [SellerWithdrawController::class, 'show']);
        Route::post('/withdraws/{id}/cancel', [SellerWithdrawController::class, 'cancel']);

        // Transfer to wallet
        Route::post('/transfer-to-wallet', [SellerWithdrawController::class, 'transferToWallet'])->middleware('throttle:5,1');

        // Seller coupons
        Route::get('/coupons', [SellerCouponController::class, 'index']);
        Route::post('/coupons', [SellerCouponController::class, 'store']);
        Route::get('/coupons/{id}', [SellerCouponController::class, 'show']);
        Route::put('/coupons/{id}', [SellerCouponController::class, 'update']);
        Route::delete('/coupons/{id}', [SellerCouponController::class, 'destroy']);

        // Support Tickets (seller)
        Route::get('/tickets', [SellerTicketController::class, 'index']);
        Route::get('/tickets/{id}', [SellerTicketController::class, 'show']);
        Route::post('/tickets/{id}/reply', [SellerTicketController::class, 'reply']);

    });

    /*
    |--------------------------------------------------------------------------
    | Coupons (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/coupons', [CouponController::class, 'index']);
        Route::get('/coupons/{id}', [CouponController::class, 'show']);
    });

    Route::post('/coupons/validate', [CouponController::class, 'validateCoupon'])
        ->middleware(['auth:sanctum', 'throttle:10,1']);

    /*
    |--------------------------------------------------------------------------
    | Taxes
    |--------------------------------------------------------------------------
    */
    Route::get('/taxes', [TaxController::class, 'index']);
    Route::middleware('auth:sanctum')->post('/taxes/calculate', [TaxController::class, 'calculate']);
    Route::middleware(['auth:sanctum', 'api.role:seller'])->get('/seller/taxes', [TaxController::class, 'sellerTaxes']);

    /*
    |--------------------------------------------------------------------------
    | Sliders
    |--------------------------------------------------------------------------
    */
    Route::get('/sliders', [SliderController::class, 'index']);
    Route::get('/sliders/type/{type}', [SliderController::class, 'byType']);
    Route::get('/sliders/{id}', [SliderController::class, 'show']);
    Route::post('/sliders/{id}/click', [SliderController::class, 'trackClick']);

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
    Route::get('/blog/{slug}', [BlogController::class, 'show']);

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
    Route::middleware('auth:sanctum')->prefix('wallet')->group(function () {
        Route::get('/', [WalletController::class, 'show']);
        Route::get('/summary', [WalletController::class, 'summary']);
        Route::get('/settings', [WalletController::class, 'settings']);
        Route::get('/transactions', [WalletController::class, 'transactions']);
        Route::get('/transactions/{id}', [WalletController::class, 'transactionShow']);
        Route::get('/deposits', [WalletController::class, 'deposits']);

        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/deposit', [WalletController::class, 'deposit']);
            Route::post('/deposit/confirm', [WalletController::class, 'confirmDeposit']);
            Route::post('/pay', [WalletController::class, 'pay']);
            Route::post('/transfer', [WalletController::class, 'transfer']);
            Route::post('/withdraw', [WalletController::class, 'withdraw']);
            Route::post('/activate', [WalletController::class, 'activate']);
            Route::post('/refund', [WalletController::class, 'refund']);
        });
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
        Route::get('/profile', [UserProfileController::class, 'show']);
        Route::post('/profile', [UserProfileController::class, 'storeOrUpdate']);
    });

    /*
    |--------------------------------------------------------------------------
    | User Addresses (Authenticated Customer/Seller)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'api.role:customer,seller'])->prefix('addresses')->group(function () {
        Route::get('/', [UserAddressController::class, 'index']);
        Route::post('/', [UserAddressController::class, 'store']);
        Route::get('/{id}', [UserAddressController::class, 'show']);
        Route::put('/{id}', [UserAddressController::class, 'update']);
        Route::delete('/{id}', [UserAddressController::class, 'destroy']);
        Route::post('/{id}/default', [UserAddressController::class, 'setDefault']);
    });

    /*
    |--------------------------------------------------------------------------
    | Wishlist (Authenticated Customer/Seller)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'api.role:customer,seller'])->prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/', [WishlistController::class, 'store']);
        Route::delete('/{productId}', [WishlistController::class, 'destroy']);
        Route::get('/check/{productId}', [WishlistController::class, 'check']);
        Route::delete('/', [WishlistController::class, 'clear']);
        Route::get('/count', [WishlistController::class, 'count']);
    });

    /*
    |--------------------------------------------------------------------------
    | Recently Viewed Products (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->prefix('recently-viewed')->group(function () {
        Route::get('/', [RecentlyViewedController::class, 'index']);
        Route::post('/', [RecentlyViewedController::class, 'store']);
        Route::delete('/', [RecentlyViewedController::class, 'clear']);
    });

    /*
    |--------------------------------------------------------------------------
    | Price & Stock Alerts (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->prefix('alerts')->group(function () {
        Route::get('/', [PriceAlertController::class, 'index']);
        Route::post('/', [PriceAlertController::class, 'store']);
        Route::put('/{id}', [PriceAlertController::class, 'update']);
        Route::delete('/{id}', [PriceAlertController::class, 'destroy']);
        Route::get('/product/{productId}', [PriceAlertController::class, 'check']);
    });

    /*
    |--------------------------------------------------------------------------
    | Ticket Departments (Public)
    |--------------------------------------------------------------------------
    */
    Route::get('/ticket-departments', [ApiTicketDepartmentController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Affiliate Program
    |--------------------------------------------------------------------------
    */
    Route::get('/affiliate/program-info', [ApiAffiliateController::class, 'programInfo']);
    Route::post('/affiliate/track', [AffiliateTrackingController::class, 'track'])
        ->middleware('throttle:120,1');

    Route::middleware('auth:sanctum')->prefix('affiliate')->group(function () {
        Route::post('/apply', [ApiAffiliateController::class, 'apply']);
        Route::get('/status', [ApiAffiliateController::class, 'status']);
        Route::get('/dashboard', [ApiAffiliateController::class, 'dashboard']);
        Route::get('/referral-link', [ApiAffiliateController::class, 'referralLink']);
        Route::get('/commissions', [ApiAffiliateController::class, 'commissions']);
        Route::get('/referrals', [ApiAffiliateController::class, 'referrals']);
        Route::get('/balance', [ApiAffiliateController::class, 'balance']);
        Route::get('/withdrawals', [ApiAffiliateController::class, 'withdrawals']);
        Route::post('/withdraw', [ApiAffiliateController::class, 'withdraw']);
        Route::post('/transfer-to-wallet', [ApiAffiliateController::class, 'transferToWallet']);
        Route::put('/profile', [ApiAffiliateController::class, 'updateProfile']);
        Route::get('/analytics', [ApiAffiliateController::class, 'analytics']);
    });

    /*
    |--------------------------------------------------------------------------
    | Support Tickets (Customer)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->prefix('tickets')->group(function () {
        Route::get('/', [ApiSupportTicketController::class, 'index']);
        Route::post('/', [ApiSupportTicketController::class, 'store']);
        Route::get('/{id}', [ApiSupportTicketController::class, 'show']);
        Route::post('/{id}/reply', [ApiSupportTicketController::class, 'reply']);
        Route::post('/{id}/escalate', [ApiSupportTicketController::class, 'escalate']);
        Route::post('/{id}/close', [ApiSupportTicketController::class, 'close']);
    });

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */
    Route::get('/settings', [SettingController::class, 'index']);
    Route::get('/settings/bootstrap', [SettingController::class, 'bootstrap']);
    Route::get('/settings/currencies', [SettingController::class, 'currencies']);
    Route::get('/settings/{group}', [SettingController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Website Pages (Homepage, Shop, Footer)
    |--------------------------------------------------------------------------
    */
    Route::get('/website/homepage', [ApiWebsiteController::class, 'homepage']);
    Route::get('/website/homepage/{section}', [ApiWebsiteController::class, 'homepageSection']);
    Route::get('/website/shop', [ApiWebsiteController::class, 'shoppage']);
    Route::get('/website/footer', [ApiWebsiteController::class, 'footer']);

    /*
    |--------------------------------------------------------------------------
    | Menus
    |--------------------------------------------------------------------------
    */
    Route::get('/menus', [MenuApiController::class, 'index']);
    Route::get('/menus/{location}', [MenuApiController::class, 'byLocation']);

    /*
    |--------------------------------------------------------------------------
    | FAQ
    |--------------------------------------------------------------------------
    */
    Route::get('/faq', [FaqApiController::class, 'index']);
    Route::get('/faq/{slug}', [FaqApiController::class, 'byCategory']);

    /*
    |--------------------------------------------------------------------------
    | Static Pages (About, Contact, Privacy, Terms)
    |--------------------------------------------------------------------------
    */
    Route::get('/page/about', [StaticPageApiController::class, 'about']);
    Route::get('/page/contact', [StaticPageApiController::class, 'contact']);
    Route::get('/page/privacy', [StaticPageApiController::class, 'privacy']);
    Route::get('/page/terms', [StaticPageApiController::class, 'terms']);

    /*
    |--------------------------------------------------------------------------
    | Newsletter Subscription
    |--------------------------------------------------------------------------
    */
    Route::post('/subscribe', [ApiSubscriberController::class, 'subscribe'])->middleware('throttle:5,1');
    Route::post('/unsubscribe', [ApiSubscriberController::class, 'unsubscribe'])->middleware('throttle:5,1');
    Route::get('/subscribe/check', [ApiSubscriberController::class, 'check'])->middleware('throttle:10,1');

    /*
    |--------------------------------------------------------------------------
    | Contact Form
    |--------------------------------------------------------------------------
    */
    Route::post('/contact', [ApiContactMessageController::class, 'store'])->middleware('throttle:5,1');

    /*
    |--------------------------------------------------------------------------
    | Search & Autocomplete
    |--------------------------------------------------------------------------
    */
    Route::get('/search/autocomplete', [SearchController::class, 'autocomplete']);
    Route::get('/search', [SearchController::class, 'search']);

    /*
    |--------------------------------------------------------------------------
    | Wholesale (Public)
    |--------------------------------------------------------------------------
    */
    Route::prefix('wholesale')->group(function () {
        Route::get('/products', [WholesaleController::class, 'products']);
        Route::get('/products/{productId}/offers', [WholesaleController::class, 'productOffers']);
        Route::post('/calculate', [WholesaleController::class, 'calculate']);
    });

    /*
    |--------------------------------------------------------------------------
    | Seller Storefront (Public)
    |--------------------------------------------------------------------------
    */
    Route::get('/store/{slug}', [SellerStoreController::class, 'show']);
    Route::get('/store/{slug}/reviews', [StoreReviewController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Notifications (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
        Route::get('/', [ApiNotificationController::class, 'index']);
        Route::get('/unread-count', [ApiNotificationController::class, 'unreadCount']);
        Route::post('/mark-all-read', [ApiNotificationController::class, 'markAllAsRead']);
        Route::post('/{id}/read', [ApiNotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [ApiNotificationController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Order Keys (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->prefix('my-keys')->group(function () {
        Route::get('/', [OrderKeyController::class, 'index']);
        Route::get('/order/{order}', [OrderKeyController::class, 'show']);
        Route::post('/deliveries/{delivery}/report', [OrderKeyController::class, 'reportKey'])->middleware('throttle:5,1');
    });

    /*
    |--------------------------------------------------------------------------
    | User Dashboard (Authenticated Customer/Seller)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'api.role:customer,seller'])->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index']);
    });

    /*
    |--------------------------------------------------------------------------
    | Refund Requests (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->prefix('refunds')->group(function () {
        Route::get('/', [RefundRequestController::class, 'index']);
        Route::post('/', [RefundRequestController::class, 'store']);
        Route::get('/{id}', [RefundRequestController::class, 'show']);
        Route::post('/{id}/cancel', [RefundRequestController::class, 'cancel']);
    });

    /*
    |--------------------------------------------------------------------------
    | Seller Reviews, Products & Analytics (Authenticated Seller)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'api.role:seller'])->prefix('seller')->group(function () {

        // Seller reviews
        Route::get('/reviews', [SellerReviewController::class, 'index']);
        Route::get('/reviews/summary', [SellerReviewController::class, 'summary']);
        Route::post('/reviews/{review}/reply', [SellerReviewController::class, 'reply']);

        // Seller products
        Route::get('/products', [SellerProductController::class, 'index']);
        Route::get('/products/{offerId}/stats', [SellerProductController::class, 'stats']);

        // Seller refund requests
        Route::get('/refunds', [RefundRequestController::class, 'sellerIndex']);

        // Seller analytics
        Route::prefix('analytics')->group(function () {
            Route::get('/overview', [SellerAnalyticsController::class, 'overview']);
            Route::get('/revenue', [SellerAnalyticsController::class, 'revenue']);
            Route::get('/sales', [SellerAnalyticsController::class, 'sales']);
            Route::get('/products', [SellerAnalyticsController::class, 'topProducts']);
            Route::get('/daily', [SellerAnalyticsController::class, 'daily']);
        });

    });

});
