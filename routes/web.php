<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\BlogCommentController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\SellerOfferController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductRegionController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProductRequestController;
use App\Http\Controllers\ProductWorksOnController;
use App\Http\Controllers\SellerWithdrawController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductLanguageController;
use App\Http\Controllers\ProductPlatformController;
use App\Http\Controllers\ProductDeveloperController;
use App\Http\Controllers\ProductPublisherController;



Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified','role:admin'])->name('dashboard');
// 
Route::middleware(['auth','role:admin'])->prefix('dashboard')->group(function () {

    // Profile (optional protect with 'users' or leave admin-only)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('permission:users');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('permission:users');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('permission:users');

    // Categories
    Route::resource('categories', ProductCategoryController::class)->middleware('permission:categories');
    Route::post('categories/bulk-delete', [ProductCategoryController::class, 'bulkDelete'])->name('categories.bulk-delete')->middleware('permission:categories');

    // Platforms
    Route::resource('platforms', ProductPlatformController::class)->middleware('permission:platforms');
    Route::post('platforms/bulk-delete', [ProductPlatformController::class, 'bulkDelete'])->name('platforms.bulk-delete')->middleware('permission:platforms');

    // Types
    Route::resource('types', ProductTypeController::class)->middleware('permission:types');
    Route::delete('types/bulk-delete', [ProductTypeController::class, 'bulkDelete'])->name('types.bulk-delete')->middleware('permission:types');

    // Regions
    Route::resource('regions', ProductRegionController::class)->middleware('permission:regions');
    Route::delete('regions/bulk-delete', [ProductRegionController::class, 'bulkDelete'])->name('regions.bulk-delete')->middleware('permission:regions');

    // Languages
    Route::resource('languages', ProductLanguageController::class)->middleware('permission:languages');
    Route::delete('languages/bulk-delete', [ProductLanguageController::class, 'bulkDelete'])->name('languages.bulk-delete')->middleware('permission:languages');

    // WorksOn
    Route::resource('workson', ProductWorksOnController::class)->middleware('permission:workson');
    Route::delete('workson/bulk-delete', [ProductWorksOnController::class, 'bulkDelete'])->name('workson.bulk-delete')->middleware('permission:workson');

    // Developers
    Route::resource('developers', ProductDeveloperController::class)->middleware('permission:developers');
    Route::delete('developers/bulk-delete', [ProductDeveloperController::class, 'bulkDelete'])->name('developers.bulk-delete')->middleware('permission:developers');

    // Publishers
    Route::resource('publishers', ProductPublisherController::class)->middleware('permission:publishers');
    Route::delete('publishers/bulk-delete', [ProductPublisherController::class, 'bulkDelete'])->name('publishers.bulk-delete')->middleware('permission:publishers');

    // Products
    Route::resource('products', ProductController::class)->except(['show'])->middleware('permission:products');
    Route::delete('products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete')->middleware('permission:products');
    Route::get('/products/{id}/preview', [ProductController::class, 'preview'])->name('products.preview')->middleware('permission:products');
    Route::get('/products/{product}/offers', [ProductController::class, 'offers'])->name('products.offers')->middleware('permission:products');
    Route::get('products/inactive', [ProductController::class, 'inactive'])->name('products.inactive')->middleware('permission:products');
    Route::get('products/featured', [ProductController::class, 'featured'])->name('products.featured')->middleware('permission:products');

    // Products Requests
    Route::resource('product-requests', ProductRequestController::class)->except(['show'])->middleware('permission:products');
    Route::delete('product-requests/bulk-delete', [ProductRequestController::class, 'bulkDelete'])->name('product-requests.bulk-delete')->middleware('permission:products');


    // Product Reviews
    Route::controller(ProductReviewController::class)
    ->prefix('product-reviews')
    ->name('product-reviews.')
    ->middleware('permission:products')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'show')->name('show');
        Route::post('/{id}/approve', 'approve')->name('approve');
        Route::post('/{id}/reject', 'reject')->name('reject');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });


    // Transactions
    Route::prefix('transactions')
    ->name('transactions.')
    ->middleware('permission:transactions')
    ->group(function () {

        Route::get('/', [TransactionController::class, 'index'])
            ->name('index'); // All Transactions

        Route::get('/pending', [TransactionController::class, 'pending'])
            ->name('pending'); // Pending Transactions

        Route::get('/failed', [TransactionController::class, 'failed'])
            ->name('failed'); // Failed Transactions

        Route::get('/completed', [TransactionController::class, 'completed'])
            ->name('completed'); // Completed Transactions
    });





    // Sellers
    Route::resource('sellers', SellerController::class)->except(['show'])->middleware('permission:sellers');
    Route::get('sellers/pending', [SellerController::class, 'pending'])
    ->name('sellers.pending')
    ->middleware('permission:sellers');
    Route::get('sellers/suspended', [SellerController::class, 'suspended'])
    ->name('sellers.suspended')
    ->middleware('permission:sellers');
    Route::delete('sellers/bulk-delete', [SellerController::class, 'bulkDelete'])->name('sellers.bulk-delete')->middleware('permission:sellers');


    Route::get('seller-withdraws', [SellerWithdrawController::class, 'index'])
    ->name('seller-withdraws.index')
    ->middleware('permission:sellers');

    Route::get('seller-withdraws/pending', [SellerWithdrawController::class, 'pending'])
    ->name('seller-withdraws.pending')
    ->middleware('permission:sellers');



    // Seller Offers
    Route::prefix('seller-offers')
    ->name('seller-offers.')
    ->middleware('permission:seller-offers')
    ->group(function () {

        // DataTable pages
        Route::get('/', [SellerOfferController::class, 'index'])->name('index');
        Route::get('/pending', [SellerOfferController::class, 'pending'])->name('pending');
        Route::get('/rejected', [SellerOfferController::class, 'rejected'])->name('rejected');

        // CRUD
        Route::get('/create', [SellerOfferController::class, 'create'])->name('create');
        Route::post('/', [SellerOfferController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [SellerOfferController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SellerOfferController::class, 'update'])->name('update');
        Route::delete('/{id}', [SellerOfferController::class, 'destroy'])->name('destroy');
        Route::delete('/bulk-delete', [SellerOfferController::class, 'bulkDelete'])->name('bulk-delete');
    });

    // Coupons
    Route::resource('coupons', CouponController::class)
    ->except(['show'])
    ->middleware('permission:coupons');

    // Taxes
    Route::resource('taxes', TaxController::class)
    ->except(['show'])
    ->middleware('permission:taxes');

    // Payment Methods
    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])
            ->name('payment-methods.index');

    Route::get('/payment-methods/{code}', [PaymentMethodController::class, 'edit'])
        ->name('payment-methods.edit');

    Route::post('/payment-methods/{code}', [PaymentMethodController::class, 'update'])
        ->name('payment-methods.update');

    

    // Currencies
    Route::resource('currencies', CurrencyController::class)->middleware('permission:currencies');
    Route::delete('currencies/bulk-delete', [CurrencyController::class, 'bulkDelete'])->name('currencies.bulk-delete')->middleware('permission:currencies');
    Route::post('/currencies/update-rates', [CurrencyController::class, 'updateRates'])->name('currencies.updateRates')->middleware('permission:currencies');

    // Orders
    Route::resource('orders', OrderController::class)->middleware('permission:orders');
    Route::delete('orders/bulk-delete', [OrderController::class, 'bulkDelete'])->name('orders.bulk-delete')->middleware('permission:orders');


    // sliders
    Route::resource('sliders', SliderController::class)
    ->except(['show'])
    ->middleware('permission:sliders');
    Route::delete('sliders/bulk-delete', [SliderController::class, 'bulkDelete'])
    ->name('sliders.bulk-delete')
    ->middleware('permission:sliders');

    // Pages
    Route::resource('pages', PageController::class)
    ->except(['show'])
    ->middleware('permission:pages');
    Route::delete('pages/bulk-delete', [PageController::class, 'bulkDelete'])
            ->name('pages.bulk-delete');

    //blog categories
    Route::delete('blog-categories/bulk-delete', [BlogCategoryController::class, 'bulkDelete'])
    ->name('blog-categories.bulk-delete');
    Route::resource('blog-categories', BlogCategoryController::class)
    ->except(['show']);

    // Blogs
    Route::get('blogs/popular', [BlogController::class, 'popular'])
    ->name('blogs.popular');
    Route::resource('blogs', BlogController::class)
    ->except(['show']);

    // Blog Comments
    Route::get('blog-comments', [BlogCommentController::class, 'index'])
            ->name('blog-comments.index');
    Route::put('blog-comments/{blogComment}/approve', [BlogCommentController::class, 'approve'])
        ->name('blog-comments.approve');
    Route::delete('blog-comments/{blogComment}', [BlogCommentController::class, 'destroy'])
        ->name('blog-comments.destroy');


    // Wallets
    Route::get('/wallets', [WalletController::class, 'index'])
    ->name('wallets.index')
    ->middleware('permission:wallet');
    Route::get('/wallets/{wallet}/transactions',[WalletController::class, 'transactions'])
    ->name('wallets.transactions')
    ->middleware('permission:wallet');
    Route::get('/wallet/history', [WalletController::class, 'history'])
    ->name('wallet.history');

    Route::post('/wallet/{user}/credit', [WalletController::class, 'credit'])
    ->name('wallet.credit');

    Route::post('/wallet/{user}/debit', [WalletController::class, 'debit'])
        ->name('wallet.debit');

    Route::get('/wallets/transactions',[WalletController::class, 'all_transactions'])
    ->name('wallets.all.transactions');






    // Roles
    Route::resource('roles', RoleController::class)->middleware('permission:roles');
    Route::delete('roles/bulk-delete', [RoleController::class, 'bulkDelete'])->name('roles.bulk-delete')->middleware('permission:roles');
    Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermission'])->name('roles.assign-permissions')->middleware('permission:roles');

    // Permissions
    Route::resource('permissions', PermissionController::class)->middleware('permission:permissions');
    Route::post('permissions/bulk-delete', [PermissionController::class, 'bulkDelete'])->name('permissions.bulk-delete')->middleware('permission:permissions');

    // Users
    Route::resource('users', UserController::class)->middleware('permission:users');
    Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete')->middleware('permission:users');
});


require __DIR__.'/auth.php';
