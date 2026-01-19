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
use App\Http\Controllers\ProductLabelsController;
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



/*
|--------------------------------------------------------------------------
| Public Entry & Super Admin Authentication Routes
|--------------------------------------------------------------------------
| - Redirects the application root URL to the login page
| - Provides a dedicated login entry for Super Admin users
|--------------------------------------------------------------------------
*/

// Redirect root URL to default login page
Route::get('/', function () {
    return redirect()->route('login');
});

// Super Admin login page (separate from standard user login)
Route::get('/superadmin-login', [ProfileController::class, 'superAdminLogin'])
    ->name('superadmin.login');



/*
|--------------------------------------------------------------------------
| Admin Dashboard Route
|--------------------------------------------------------------------------
| Displays the admin dashboard.
| Access is restricted to:
| - Authenticated users
| - Verified email accounts
| - Users with the Admin role
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

});


/*
|--------------------------------------------------------------------------
| Admin Dashboard Routes
|--------------------------------------------------------------------------
| All routes inside this group:
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:admin'])->prefix('dashboard')->group(function () {


    /*
    |--------------------------------------------------------------------------
    | User Profile Management Routes
    |--------------------------------------------------------------------------
    | These routes allow users/admins to manage their own profile
    | including editing details, updating information, and
    | permanently deleting the account.
    | Access is restricted using the `users` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:users')->group(function () {

        // Show profile edit form
        Route::get('profile', [ProfileController::class, 'edit'])
            ->name('profile.edit');

        // Update profile information
        Route::patch('profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        // Delete user profile/account
        Route::delete('profile', [ProfileController::class, 'destroy'])
            ->name('profile.destroy');

    });


    /*
    |--------------------------------------------------------------------------
    | Product Category Management Routes
    |--------------------------------------------------------------------------
    | These routes manage product categories including
    | CRUD operations and bulk actions.
    | All routes are protected by the `categories` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:categories')->group(function () {

        // Category CRUD (index, create, store, edit, update, destroy)
        Route::resource('categories', ProductCategoryController::class);

        // Bulk delete multiple categories
        Route::post('categories/bulk-delete', [ProductCategoryController::class, 'bulkDelete'])
            ->name('categories.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | Product Platform Management Routes
    |--------------------------------------------------------------------------
    | These routes handle platform-related CRUD operations
    | and bulk management actions.
    | All routes are protected by the `platforms` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:platforms')->group(function () {

        // Platform CRUD (index, create, store, edit, update, destroy)
        Route::resource('platforms', ProductPlatformController::class);

        // Bulk delete multiple platforms
        Route::post('platforms/bulk-delete', [ProductPlatformController::class, 'bulkDelete'])
            ->name('platforms.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | Product Type Management Routes
    |--------------------------------------------------------------------------
    | These routes manage product types including
    | CRUD operations and bulk actions.
    | All routes are protected by the `types` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:types')->group(function () {

        // Product Type CRUD (index, create, store, edit, update, destroy)
        Route::resource('types', ProductTypeController::class);

        // Bulk delete multiple product types
        Route::delete('types/bulk-delete', [ProductTypeController::class, 'bulkDelete'])
            ->name('types.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | Product Region Management Routes
    |--------------------------------------------------------------------------
    | These routes manage product regions including
    | CRUD operations and bulk delete actions.
    | All routes are protected by the `regions` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:regions')->group(function () {

        // Product Region CRUD (index, create, store, edit, update, destroy)
        Route::resource('regions', ProductRegionController::class);

        // Bulk delete multiple product regions
        Route::delete('regions/bulk-delete', [ProductRegionController::class, 'bulkDelete'])
            ->name('regions.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | Product Language Management Routes
    |--------------------------------------------------------------------------
    | These routes manage product languages including
    | CRUD operations and bulk delete actions.
    | All routes are protected by the `languages` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:languages')->group(function () {

        // Product Language CRUD (index, create, store, edit, update, destroy)
        Route::resource('languages', ProductLanguageController::class);

        // Bulk delete multiple product languages
        Route::delete('languages/bulk-delete', [ProductLanguageController::class, 'bulkDelete'])
            ->name('languages.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | Product WorksOn Management Routes
    |--------------------------------------------------------------------------
    | These routes manage “Works On” attributes
    | (e.g., OS, device, or platform compatibility).
    | Includes CRUD operations and bulk delete actions.
    | All routes are protected by the `workson` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:workson')->group(function () {

        // Product WorksOn CRUD (index, create, store, edit, update, destroy)
        Route::resource('workson', ProductWorksOnController::class);

        // Bulk delete multiple WorksOn entries
        Route::delete('workson/bulk-delete', [ProductWorksOnController::class, 'bulkDelete'])
            ->name('workson.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | Product Developer Management Routes
    |--------------------------------------------------------------------------
    | These routes manage product developers including
    | CRUD operations and bulk delete actions.
    | All routes are protected by the `developers` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:developers')->group(function () {

        // Product Developer CRUD (index, create, store, edit, update, destroy)
        Route::resource('developers', ProductDeveloperController::class);

        // Bulk delete multiple product developers
        Route::delete('developers/bulk-delete', [ProductDeveloperController::class, 'bulkDelete'])
            ->name('developers.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | Product Publisher Management Routes
    |--------------------------------------------------------------------------
    | These routes manage product publishers including
    | CRUD operations and bulk delete actions.
    | All routes are protected by the `publishers` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:publishers')->group(function () {

        // Product Publisher CRUD (index, create, store, edit, update, destroy)
        Route::resource('publishers', ProductPublisherController::class);

        // Bulk delete multiple product publishers
        Route::delete('publishers/bulk-delete', [ProductPublisherController::class, 'bulkDelete'])
            ->name('publishers.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | Product Labels Management Routes
    |--------------------------------------------------------------------------
    | These routes manage product labels including
    | CRUD operations and bulk delete actions.
    | All routes are protected by the `labels` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:labels')->group(function () {

        // Product Label CRUD (index, create, store, edit, update, destroy)
        Route::resource('labels', ProductLabelsController::class);

        // Bulk delete multiple product labels  
        Route::delete('labels/bulk-delete', [ProductLabelsController::class, 'bulkDelete'])
            ->name('labels.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | Product Management Routes
    |--------------------------------------------------------------------------
    | These routes handle all product-related CRUD operations,
    | bulk actions, and special product views.
    | All routes are protected by the `products` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:products')->group(function () {

        // Product CRUD (except show)
        Route::resource('products', ProductController::class)
            ->except(['show']);

        // Bulk delete multiple products
        Route::delete('products/bulk-delete', [ProductController::class, 'bulkDelete'])
            ->name('products.bulk-delete');

        // Preview a product (admin/internal view)
        Route::get('products/{id}/preview', [ProductController::class, 'preview'])
            ->name('products.preview');

        // View offers related to a specific product
        Route::get('products/{product}/offers', [ProductController::class, 'offers'])
            ->name('products.offers');

        // List inactive products
        Route::get('products/inactive', [ProductController::class, 'inactive'])
            ->name('products.inactive');

        // List featured products
        Route::get('products/featured', [ProductController::class, 'featured'])
            ->name('products.featured');

    });




    /*
    |--------------------------------------------------------------------------
    | Product Request Management Routes
    |--------------------------------------------------------------------------
    | These routes handle customer/admin product requests
    | including review, approval, updates, and bulk deletion.
    | Access is restricted by the `products` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:products')->group(function () {

        // Product Request CRUD (except show)
        Route::resource('product-requests', ProductRequestController::class)
            ->except(['show']);

        // Bulk delete multiple product requests
        Route::delete('product-requests/bulk-delete', [ProductRequestController::class, 'bulkDelete'])
            ->name('product-requests.bulk-delete');

    });



    /*
    |--------------------------------------------------------------------------
    | Product Review Management Routes
    |--------------------------------------------------------------------------
    | These routes handle moderation of product reviews:
    | - Listing and viewing reviews
    | - Approving or rejecting reviews
    | - Deleting reviews
    | All routes are protected by the `products` permission.
    |--------------------------------------------------------------------------
    */

    Route::controller(ProductReviewController::class)
        ->prefix('product-reviews')
        ->name('product-reviews.')
        ->middleware('permission:products')
        ->group(function () {

            // List all product reviews
            Route::get('/', 'index')->name('index');

            // View a single product review
            Route::get('{review}', 'show')->name('show');

            // Approve a product review
            Route::post('{review}/approve', 'approve')->name('approve');

            // Reject a product review
            Route::post('{review}/reject', 'reject')->name('reject');

            // Delete a product review
            Route::delete('{review}', 'destroy')->name('destroy');

        });



    /*
    |--------------------------------------------------------------------------
    | Transaction Management Routes
    |--------------------------------------------------------------------------
    | These routes handle viewing and filtering transactions
    | by status (all, pending, failed, completed).
    | Access is restricted by the `transactions` permission.
    |--------------------------------------------------------------------------
    */

    Route::prefix('transactions')
        ->name('transactions.')
        ->middleware('permission:transactions')
        ->group(function () {

            // View all transactions
            Route::get('/', [TransactionController::class, 'index'])
                ->name('index');

            // View pending transactions
            Route::get('pending', [TransactionController::class, 'pending'])
                ->name('pending');

            // View failed transactions
            Route::get('failed', [TransactionController::class, 'failed'])
                ->name('failed');

            // View completed transactions
            Route::get('completed', [TransactionController::class, 'completed'])
                ->name('completed');

        });






    /*
    |--------------------------------------------------------------------------
    | Seller Management Routes
    |--------------------------------------------------------------------------
    | These routes manage sellers including:
    | - CRUD operations (except show)
    | - Viewing sellers by status (pending, suspended)
    | - Bulk delete actions
    | Access is restricted by the `sellers` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:sellers')->group(function () {

        // Seller CRUD (except show)
        Route::resource('sellers', SellerController::class)
            ->except(['show']);

        // View pending sellers
        Route::get('sellers/pending', [SellerController::class, 'pending'])
            ->name('sellers.pending');

        // View suspended sellers
        Route::get('sellers/suspended', [SellerController::class, 'suspended'])
            ->name('sellers.suspended');

        // Bulk delete multiple sellers
        Route::delete('sellers/bulk-delete', [SellerController::class, 'bulkDelete'])
            ->name('sellers.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | Seller Offer Management Routes
    |--------------------------------------------------------------------------
    | These routes manage seller offers such as
    | activating or deactivating an offer.
    |--------------------------------------------------------------------------
    */

    Route::post('seller-offers/{offer}/toggle-status', [SellerOfferController::class, 'toggleStatus'])
        ->name('seller-offers.toggle-status');



    /*
    |--------------------------------------------------------------------------
    | Seller Withdraw Management Routes
    |--------------------------------------------------------------------------
    | These routes allow admins to view and manage
    | seller withdrawal requests.
    | Access is restricted by the `sellers` permission.
    |--------------------------------------------------------------------------
    */

    Route::prefix('seller-withdraws')
        ->name('seller-withdraws.')
        ->middleware('permission:sellers')
        ->group(function () {

            // View all seller withdraw requests
            Route::get('/', [SellerWithdrawController::class, 'index'])
                ->name('index');

            // View pending seller withdraw requests
            Route::get('pending', [SellerWithdrawController::class, 'pending'])
                ->name('pending');

        });




    /*
    |--------------------------------------------------------------------------
    | Seller Offer Management Routes
    |--------------------------------------------------------------------------
    | These routes manage seller product offers including:
    | - Listing offers by status (all, pending, rejected)
    | - Creating and updating offers
    | - Deleting single or multiple offers
    | Access is restricted by the `seller-offers` permission.
    |--------------------------------------------------------------------------
    */

    Route::prefix('seller-offers')
        ->name('seller-offers.')
        ->middleware('permission:seller-offers')
        ->group(function () {

            /*
            |------------------------------------------------------------------
            | Listing & Filters
            |------------------------------------------------------------------
            */

            // List all seller offers
            Route::get('/', [SellerOfferController::class, 'index'])
                ->name('index');

            // List pending seller offers
            Route::get('pending', [SellerOfferController::class, 'pending'])
                ->name('pending');

            // List rejected seller offers
            Route::get('rejected', [SellerOfferController::class, 'rejected'])
                ->name('rejected');


            /*
            |------------------------------------------------------------------
            | CRUD Operations
            |------------------------------------------------------------------
            */

            // Show create form
            Route::get('create', [SellerOfferController::class, 'create'])
                ->name('create');

            // Store new seller offer
            Route::post('/', [SellerOfferController::class, 'store'])
                ->name('store');

            // Show edit form
            Route::get('{offer}/edit', [SellerOfferController::class, 'edit'])
                ->name('edit');

            // Update seller offer
            Route::put('{offer}', [SellerOfferController::class, 'update'])
                ->name('update');

            // Delete a seller offer
            Route::delete('{offer}', [SellerOfferController::class, 'destroy'])
                ->name('destroy');

            // Bulk delete seller offers
            Route::delete('bulk-delete', [SellerOfferController::class, 'bulkDelete'])
                ->name('bulk-delete');

        });


    /*
    |--------------------------------------------------------------------------
    | Coupon Management Routes
    |--------------------------------------------------------------------------
    | These routes manage discount coupons including
    | creation, editing, updating, and deletion.
    | Access is restricted by the `coupons` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:coupons')->group(function () {

        // Coupon CRUD (except show)
        Route::resource('coupons', CouponController::class)
            ->except(['show']);

    });


    /*
    |--------------------------------------------------------------------------
    | Tax Management Routes
    |--------------------------------------------------------------------------
    | These routes manage tax rules and configurations
    | including creation, updates, and deletion.
    | Access is restricted by the `taxes` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:taxes')->group(function () {

        // Tax CRUD (except show)
        Route::resource('taxes', TaxController::class)
            ->except(['show']);

    });


    /*
    |--------------------------------------------------------------------------
    | Payment Method Configuration Routes
    |--------------------------------------------------------------------------
    | These routes manage available payment methods
    | and their configuration settings.
    |--------------------------------------------------------------------------
    */

    Route::prefix('payment-methods')
        ->name('payment-methods.')
        ->group(function () {

            // List all payment methods
            Route::get('/', [PaymentMethodController::class, 'index'])
                ->name('index');

            // Show edit form for a payment method (by code)
            Route::get('{code}', [PaymentMethodController::class, 'edit'])
                ->name('edit');

            // Update payment method configuration
            Route::post('{code}', [PaymentMethodController::class, 'update'])
                ->name('update');

        });


    

    /*
    |--------------------------------------------------------------------------
    | Currency Management Routes
    |--------------------------------------------------------------------------
    | These routes manage system currencies including:
    | - CRUD operations
    | - Bulk deletion
    | - Exchange rate updates
    | Access is restricted by the `currencies` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:currencies')->group(function () {

        // Currency CRUD
        Route::resource('currencies', CurrencyController::class);

        // Bulk delete currencies
        Route::delete('currencies/bulk-delete', [CurrencyController::class, 'bulkDelete'])
            ->name('currencies.bulk-delete');

        // Update currency exchange rates
        Route::post('currencies/update-rates', [CurrencyController::class, 'updateRates'])
            ->name('currencies.updateRates');

    });


    /*
    |--------------------------------------------------------------------------
    | Order Management Routes
    |--------------------------------------------------------------------------
    | These routes manage customer orders including:
    | - Viewing and updating orders
    | - Bulk deletion
    | Access is restricted by the `orders` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:orders')->group(function () {

        // Order CRUD
        Route::resource('orders', OrderController::class);

        // Bulk delete orders
        Route::delete('orders/bulk-delete', [OrderController::class, 'bulkDelete'])
            ->name('orders.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | Slider Management Routes
    |--------------------------------------------------------------------------
    | These routes manage homepage sliders including
    | CRUD operations and bulk deletion.
    | Access is restricted by the `sliders` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:sliders')->group(function () {

        // Slider CRUD (except show)
        Route::resource('sliders', SliderController::class)
            ->except(['show']);

        // Bulk delete sliders
        Route::delete('sliders/bulk-delete', [SliderController::class, 'bulkDelete'])
            ->name('sliders.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | Page Management Routes
    |--------------------------------------------------------------------------
    | These routes manage CMS pages including
    | creation, updates, and deletion.
    | Access is restricted by the `pages` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:pages')->group(function () {

        // Page CRUD (except show)
        Route::resource('pages', PageController::class)
            ->except(['show']);

        // Bulk delete pages
        Route::delete('pages/bulk-delete', [PageController::class, 'bulkDelete'])
            ->name('pages.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | Blog Category Management Routes
    |--------------------------------------------------------------------------
    | These routes manage blog categories including
    | CRUD operations and bulk deletion.
    |--------------------------------------------------------------------------
    */

    Route::prefix('blog-categories')
        ->name('blog-categories.')
        ->group(function () {

            // Blog category CRUD (except show)
            Route::resource('/', BlogCategoryController::class)
                ->except(['show'])
                ->parameters(['' => 'blogCategory']);

            // Bulk delete blog categories
            Route::delete('bulk-delete', [BlogCategoryController::class, 'bulkDelete'])
                ->name('bulk-delete');

        });


    /*
    |--------------------------------------------------------------------------
    | Blog Management Routes
    |--------------------------------------------------------------------------
    | These routes manage blogs including
    | listing, creation, editing, and deletion.
    |--------------------------------------------------------------------------
    */

    Route::prefix('blogs')
        ->name('blogs.')
        ->group(function () {

            // List popular blogs
            Route::get('popular', [BlogController::class, 'popular'])
                ->name('popular');

            // Blog CRUD (except show)
            Route::resource('/', BlogController::class)
                ->except(['show'])
                ->parameters(['' => 'blog']);

        });


    /*
    |--------------------------------------------------------------------------
    | Blog Comment Moderation Routes
    |--------------------------------------------------------------------------
    | These routes allow admins to moderate blog comments
    | including approval and deletion.
    |--------------------------------------------------------------------------
    */

    Route::prefix('blog-comments')
        ->name('blog-comments.')
        ->group(function () {

            // List all blog comments
            Route::get('/', [BlogCommentController::class, 'index'])
                ->name('index');

            // Approve a blog comment
            Route::put('{blogComment}/approve', [BlogCommentController::class, 'approve'])
                ->name('approve');

            // Delete a blog comment
            Route::delete('{blogComment}', [BlogCommentController::class, 'destroy'])
                ->name('destroy');

        });


    /*
    |--------------------------------------------------------------------------
    | Wallet Management Routes
    |--------------------------------------------------------------------------
    | These routes manage user wallets including:
    | - Viewing wallets and transactions
    | - Crediting and debiting balances
    | - Viewing wallet history
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:wallet')->group(function () {

        // List all wallets
        Route::get('wallets', [WalletController::class, 'index'])
            ->name('wallets.index');

        // View transactions of a specific wallet
        Route::get('wallets/{wallet}/transactions', [WalletController::class, 'transactions'])
            ->name('wallets.transactions');

        // View all wallet transactions
        Route::get('wallets/transactions', [WalletController::class, 'all_transactions'])
            ->name('wallets.all.transactions');

        // Wallet history (global)
        Route::get('wallet/history', [WalletController::class, 'history'])
            ->name('wallet.history');

        // Credit user wallet
        Route::post('wallet/{user}/credit', [WalletController::class, 'credit'])
            ->name('wallet.credit');

        // Debit user wallet
        Route::post('wallet/{user}/debit', [WalletController::class, 'debit'])
            ->name('wallet.debit');

    });



    /*
    |--------------------------------------------------------------------------
    | Role Management Routes
    |--------------------------------------------------------------------------
    | These routes manage system roles including:
    | - CRUD operations
    | - Bulk deletion
    | - Assigning permissions to roles
    | Access is restricted by the `roles` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:roles')->group(function () {

        // Role CRUD
        Route::resource('roles', RoleController::class);

        // Bulk delete roles
        Route::delete('roles/bulk-delete', [RoleController::class, 'bulkDelete'])
            ->name('roles.bulk-delete');

        // Assign permissions to a role
        Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermission'])
            ->name('roles.assign-permissions');

    });


    /*
    |--------------------------------------------------------------------------
    | Permission Management Routes
    |--------------------------------------------------------------------------
    | These routes manage permissions including
    | creation, updates, and bulk deletion.
    | Access is restricted by the `permissions` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:permissions')->group(function () {

        // Permission CRUD
        Route::resource('permissions', PermissionController::class);

        // Bulk delete permissions
        Route::post('permissions/bulk-delete', [PermissionController::class, 'bulkDelete'])
            ->name('permissions.bulk-delete');

    });


    /*
    |--------------------------------------------------------------------------
    | User Management Routes
    |--------------------------------------------------------------------------
    | These routes manage system users including:
    | - CRUD operations
    | - Customer listing
    | - Bulk deletion
    | Access is restricted by the `users` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:users')->group(function () {

        // User CRUD
        Route::resource('users', UserController::class);

        // List customers only
        Route::get('customer', [UserController::class, 'customer'])
            ->name('customer.index');

        // Bulk delete users
        Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])
            ->name('users.bulk-delete');

    });




});


require __DIR__.'/auth.php';
