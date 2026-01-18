<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    TaxController,
    BlogController,
    PageController,
    RoleController,
    UserController,
    OrderController,
    CouponController,
    SellerController,
    SliderController,
    WalletController,
    ProductController,
    ProfileController,
    CurrencyController,
    PermissionController,
    BlogCommentController,
    ProductTypeController,
    SellerOfferController,
    TransactionController,
    BlogCategoryController,
    PaymentMethodController,
    ProductRegionController,
    ProductReviewController,
    ProductRequestController,
    ProductWorksOnController,
    SellerWithdrawController,
    ProductCategoryController,
    ProductLanguageController,
    ProductPlatformController,
    ProductDeveloperController,
    ProductPublisherController
};

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));

Route::get('/superadmin/login', [ProfileController::class, 'superAdminLogin'])
    ->name('superadmin.login');


/*
|--------------------------------------------------------------------------
| Dashboard (Admin)
|--------------------------------------------------------------------------
*/
Route::view('/dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'role:admin'])
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('dashboard')
    ->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:users')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Product Master Data
    |--------------------------------------------------------------------------
    */
    $productMasters = [
        'categories'  => ProductCategoryController::class,
        'platforms'   => ProductPlatformController::class,
        'types'       => ProductTypeController::class,
        'regions'     => ProductRegionController::class,
        'languages'   => ProductLanguageController::class,
        'workson'     => ProductWorksOnController::class,
        'developers'  => ProductDeveloperController::class,
        'publishers'  => ProductPublisherController::class,
    ];

    foreach ($productMasters as $uri => $controller) {
        Route::resource($uri, $controller)->middleware("permission:$uri");
        Route::delete("$uri/bulk-delete", [$controller, 'bulkDelete'])
            ->name("$uri.bulk-delete")
            ->middleware("permission:$uri");
    }

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:products')->group(function () {
        Route::resource('products', ProductController::class)->except('show');
        Route::delete('products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');
        Route::get('products/{id}/preview', [ProductController::class, 'preview'])->name('products.preview');
        Route::get('products/{product}/offers', [ProductController::class, 'offers'])->name('products.offers');
        Route::get('products/inactive', [ProductController::class, 'inactive'])->name('products.inactive');
        Route::get('products/featured', [ProductController::class, 'featured'])->name('products.featured');
    });

    /*
    |--------------------------------------------------------------------------
    | Product Requests & Reviews
    |--------------------------------------------------------------------------
    */
    Route::resource('product-requests', ProductRequestController::class)
        ->except('show')
        ->middleware('permission:products');

    Route::delete('product-requests/bulk-delete', [ProductRequestController::class, 'bulkDelete'])
        ->name('product-requests.bulk-delete')
        ->middleware('permission:products');

    Route::prefix('product-reviews')
        ->name('product-reviews.')
        ->middleware('permission:products')
        ->controller(ProductReviewController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{id}', 'show')->name('show');
            Route::post('{id}/approve', 'approve')->name('approve');
            Route::post('{id}/reject', 'reject')->name('reject');
            Route::delete('{id}', 'destroy')->name('destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Transactions
    |--------------------------------------------------------------------------
    */
    Route::prefix('transactions')
        ->name('transactions.')
        ->middleware('permission:transactions')
        ->controller(TransactionController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/pending', 'pending')->name('pending');
            Route::get('/failed', 'failed')->name('failed');
            Route::get('/completed', 'completed')->name('completed');
        });

    /*
    |--------------------------------------------------------------------------
    | Sellers
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:sellers')->group(function () {
        Route::resource('sellers', SellerController::class)->except('show');
        Route::get('sellers/pending', [SellerController::class, 'pending'])->name('sellers.pending');
        Route::get('sellers/suspended', [SellerController::class, 'suspended'])->name('sellers.suspended');
        Route::delete('sellers/bulk-delete', [SellerController::class, 'bulkDelete'])->name('sellers.bulk-delete');

        Route::get('seller-withdraws', [SellerWithdrawController::class, 'index'])->name('seller-withdraws.index');
        Route::get('seller-withdraws/pending', [SellerWithdrawController::class, 'pending'])->name('seller-withdraws.pending');
    });

    /*
    |--------------------------------------------------------------------------
    | Seller Offers
    |--------------------------------------------------------------------------
    */
    Route::prefix('seller-offers')
        ->name('seller-offers.')
        ->middleware('permission:seller-offers')
        ->controller(SellerOfferController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/pending', 'pending')->name('pending');
            Route::get('/rejected', 'rejected')->name('rejected');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{id}/edit', 'edit')->name('edit');
            Route::put('{id}', 'update')->name('update');
            Route::delete('{id}', 'destroy')->name('destroy');
            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete');
        });

    /*
    |--------------------------------------------------------------------------
    | Finance
    |--------------------------------------------------------------------------
    */
    Route::resource('coupons', CouponController::class)->except('show')->middleware('permission:coupons');
    Route::resource('taxes', TaxController::class)->except('show')->middleware('permission:taxes');
    Route::resource('currencies', CurrencyController::class)->middleware('permission:currencies');
    Route::post('currencies/update-rates', [CurrencyController::class, 'updateRates'])->name('currencies.updateRates');
    Route::delete('currencies/bulk-delete', [CurrencyController::class, 'bulkDelete'])->name('currencies.bulk-delete');

    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */
    Route::resource('orders', OrderController::class)->middleware('permission:orders');
    Route::delete('orders/bulk-delete', [OrderController::class, 'bulkDelete'])->name('orders.bulk-delete');

    /*
    |--------------------------------------------------------------------------
    | CMS
    |--------------------------------------------------------------------------
    */
    Route::resource('sliders', SliderController::class)->except('show')->middleware('permission:sliders');
    Route::delete('sliders/bulk-delete', [SliderController::class, 'bulkDelete'])->name('sliders.bulk-delete');

    Route::resource('pages', PageController::class)->except('show')->middleware('permission:pages');
    Route::delete('pages/bulk-delete', [PageController::class, 'bulkDelete'])->name('pages.bulk-delete');

    Route::resource('blog-categories', BlogCategoryController::class)->except('show');
    Route::delete('blog-categories/bulk-delete', [BlogCategoryController::class, 'bulkDelete'])->name('blog-categories.bulk-delete');

    Route::get('blogs/popular', [BlogController::class, 'popular'])->name('blogs.popular');
    Route::resource('blogs', BlogController::class)->except('show');

    Route::get('blog-comments', [BlogCommentController::class, 'index'])->name('blog-comments.index');
    Route::put('blog-comments/{blogComment}/approve', [BlogCommentController::class, 'approve'])->name('blog-comments.approve');
    Route::delete('blog-comments/{blogComment}', [BlogCommentController::class, 'destroy'])->name('blog-comments.destroy');

    /*
    |--------------------------------------------------------------------------
    | Wallet
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:wallet')->group(function () {
        Route::get('wallets', [WalletController::class, 'index'])->name('wallets.index');
        Route::get('wallets/{wallet}/transactions', [WalletController::class, 'transactions'])->name('wallets.transactions');
        Route::get('wallets/transactions', [WalletController::class, 'all_transactions'])->name('wallets.all.transactions');
        Route::post('wallet/{user}/credit', [WalletController::class, 'credit'])->name('wallet.credit');
        Route::post('wallet/{user}/debit', [WalletController::class, 'debit'])->name('wallet.debit');
    });

    /*
    |--------------------------------------------------------------------------
    | Access Control
    |--------------------------------------------------------------------------
    */
    Route::resource('roles', RoleController::class)->middleware('permission:roles');
    Route::delete('roles/bulk-delete', [RoleController::class, 'bulkDelete'])->name('roles.bulk-delete');
    Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermission'])->name('roles.assign-permissions');

    Route::resource('permissions', PermissionController::class)->middleware('permission:permissions');
    Route::post('permissions/bulk-delete', [PermissionController::class, 'bulkDelete'])->name('permissions.bulk-delete');

    Route::resource('users', UserController::class)->middleware('permission:users');
    Route::get('customer', [UserController::class, 'customer'])->name('customer.index');
    Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
