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
use App\Http\Controllers\SystemController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AiContentController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardWidgetController;
use App\Http\Controllers\HeaderNotificationController;
use App\Http\Controllers\OrderNoteController;
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
use App\Http\Controllers\WalletSettingController;
use App\Http\Controllers\ProductRequestController;
use App\Http\Controllers\ProductWorksOnController;
use App\Http\Controllers\SellerWithdrawController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductLanguageController;
use App\Http\Controllers\ProductPlatformController;
use App\Http\Controllers\ProductDeveloperController;
use App\Http\Controllers\ProductPublisherController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\CannedResponseController;
use App\Http\Controllers\TicketDepartmentController;
use App\Http\Controllers\TicketNotificationSettingsController;
use App\Http\Controllers\OrderNotificationSettingsController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\AffiliateCommissionController;
use App\Http\Controllers\AffiliateWithdrawalController;
use App\Http\Controllers\AffiliateTierController;



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

Route::get('/system/optimize', [SystemController::class, 'optimize']);

if (app()->isLocal()) {
    Route::get('/superadmin-login', [ProfileController::class, 'superAdminLogin'])
        ->name('superadmin.login');
}



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

Route::middleware(['auth', 'verified', 'role:internal', 'restrict.delete'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

    Route::prefix('dashboard/widgets')->name('dashboard.widgets.')->group(function () {
        Route::get('/statistics', [DashboardWidgetController::class, 'statistics'])->name('statistics');
        Route::get('/view-sales', [DashboardWidgetController::class, 'viewSales'])->name('view-sales');
        Route::get('/profit', [DashboardWidgetController::class, 'profit'])->name('profit');
        Route::get('/expenses', [DashboardWidgetController::class, 'expenses'])->name('expenses');
        Route::get('/revenue-report', [DashboardWidgetController::class, 'revenueReport'])->name('revenue-report');
        Route::get('/earning-report', [DashboardWidgetController::class, 'earningReport'])->name('earning-report');
        Route::get('/popular-products', [DashboardWidgetController::class, 'popularProducts'])->name('popular-products');
        Route::get('/recent-orders', [DashboardWidgetController::class, 'recentOrders'])->name('recent-orders');
        Route::get('/recent-transactions', [DashboardWidgetController::class, 'recentTransactions'])->name('recent-transactions');
        Route::get('/generated-leads', [DashboardWidgetController::class, 'generatedLeads'])->name('generated-leads');
        Route::get('/invoices', [DashboardWidgetController::class, 'invoices'])->name('invoices');
    });

    Route::prefix('header/notifications')->name('header.notifications')->group(function () {
        Route::get('/', [HeaderNotificationController::class, 'index']);
        Route::post('/{id}/read', [HeaderNotificationController::class, 'markAsRead'])->name('.read');
        Route::post('/read-all', [HeaderNotificationController::class, 'markAllAsRead'])->name('.read-all');
    });

});


/*
|--------------------------------------------------------------------------
| Admin Dashboard Routes
|--------------------------------------------------------------------------
| All routes inside this group:
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:internal','restrict.delete'])->prefix('dashboard')->group(function () {


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

    Route::get('profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::put('profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password');

    Route::delete('profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::post('profile/address', [ProfileController::class, 'storeAddress'])
        ->name('profile.address.store');
    Route::put('profile/address/{address}', [ProfileController::class, 'updateAddress'])
        ->name('profile.address.update');
    Route::delete('profile/address/{address}', [ProfileController::class, 'destroyAddress'])
        ->name('profile.address.destroy');
    Route::patch('profile/address/{address}/default', [ProfileController::class, 'setDefaultAddress'])
        ->name('profile.address.default');


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

        Route::post('products/bulk-status',   [ProductController::class, 'bulkStatus'])->name('products.bulk-status');
        Route::post('products/bulk-featured', [ProductController::class, 'bulk-featured'])->name('products.bulk-featured');
        Route::post('products/bulk-delete',   [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');

        // Preview a product (admin/internal view)
        Route::get('products/{id}/preview', [ProductController::class, 'preview'])
            ->name('products.preview');

        // View offers related to a specific product
        Route::get('products/{product}/offers', [ProductController::class, 'offers'])
            ->name('products.offers');

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

        // 🔥 BULK ACTIONS
        Route::post('/bulk-status', [ProductRequestController::class, 'bulkStatus'])
            ->name('product-requests.bulk-status');

        Route::post('/bulk-delete', [ProductRequestController::class, 'bulkDelete'])
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

            Route::delete('{review}', 'destroy')->name('destroy');

            Route::post('bulk-status', 'bulkStatus')->name('bulk-status');
            Route::post('bulk-delete', 'bulkDelete')->name('bulk-delete');

        });
    



    /*
    |--------------------------------------------------------------------------
    | Transaction Management Routes
    |--------------------------------------------------------------------------
    | View, filter, and manage transactions.
    | Includes bulk actions and single record operations.
    | Access controlled via `transactions` permission.
    |--------------------------------------------------------------------------
    */

    Route::prefix('transactions')
        ->name('transactions.')
        ->middleware('permission:transactions')
        ->group(function () {

            /* ===============================
            * Views
            =============================== */

            // All transactions
            Route::get('/', [TransactionController::class, 'index'])
                ->name('index');

            // Pending transactions
            Route::get('pending', [TransactionController::class, 'pending'])
                ->name('pending');

            // Failed transactions
            Route::get('failed', [TransactionController::class, 'failed'])
                ->name('failed');

            // Completed transactions
            Route::get('completed', [TransactionController::class, 'completed'])
                ->name('completed');


            /* ===============================
            * Bulk Actions
            =============================== */

            // Bulk status update (pending → completed / failed / reversed)
            Route::post('bulk-status', [TransactionController::class, 'bulkStatus'])
                ->name('bulk-status');

            // Bulk delete (soft delete recommended)
            Route::post('bulk-delete', [TransactionController::class, 'bulkDelete'])
                ->name('bulk-delete');


            /* ===============================
            * Single Transaction Actions
            =============================== */

            // View transaction details
            Route::get('{transaction}', [TransactionController::class, 'show'])
                ->name('show');

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

        Route::get('sellers/pending', [SellerController::class, 'pending'])
            ->name('sellers.pending');

        Route::get('sellers/suspended', [SellerController::class, 'suspended'])
            ->name('sellers.suspended');

        Route::post('sellers/bulk-delete', [SellerController::class, 'bulkDelete'])
            ->name('sellers.bulk-delete');

        Route::post('sellers/bulk-status', [SellerController::class, 'bulkStatus'])
            ->name('sellers.bulk-status');

        Route::resource('sellers', SellerController::class);

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
        ->name('seller-offers.toggle-status')
        ->middleware('permission:seller-offers');



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

            Route::get('/', [SellerWithdrawController::class, 'index'])
                ->name('index');

            Route::get('pending', [SellerWithdrawController::class, 'pending'])
                ->name('pending');

            Route::get('{withdraw}', [SellerWithdrawController::class, 'show'])
                ->name('show');

            Route::post('{withdraw}/approve', [SellerWithdrawController::class, 'approve'])
                ->name('approve');

            Route::post('{withdraw}/reject', [SellerWithdrawController::class, 'reject'])
                ->name('reject');

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

            Route::delete('{offer}', [SellerOfferController::class, 'destroy'])
                ->name('destroy');

            Route::post('/bulk-status', [SellerOfferController::class, 'bulkStatus'])
                ->name('bulk-status');

            Route::post('/bulk-delete', [SellerOfferController::class, 'bulkDelete'])
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

        Route::post('coupons/bulk-delete', [CouponController::class, 'bulkDelete'])
            ->name('coupons.bulk-delete');

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

        // Bulk delete taxes
        Route::post('taxes/bulk-delete', [TaxController::class, 'bulkDelete'])
            ->name('taxes.bulk-delete');

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

        Route::post('currencies/bulk-delete', [CurrencyController::class, 'bulkDelete'])
            ->name('currencies.bulk-delete');

        // Update currency exchange rates
        Route::post('currencies/update-rates', [CurrencyController::class, 'updateRates'])
            ->name('currencies.updateRates');

    });


    /*
    |--------------------------------------------------------------------------
    | Invoice Management Routes
    |--------------------------------------------------------------------------
    | These routes manage system invoices including:
    | - Invoice CRUD operations
    | - Invoice item handling
    | - Sending invoices
    | - PDF generation
    | - Marking invoices as paid
    | Access is restricted by the `invoices` permission.
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:invoices')->group(function () {

        // =========================
        // Invoice CRUD
        // =========================
        Route::resource('invoices', InvoiceController::class);

        // =========================
        // Invoice Actions
        // =========================

        // Send invoice to customer (email)
        Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])
            ->name('invoices.send');

        // Mark invoice as paid
        Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])
            ->name('invoices.mark-paid');

        // Print invoice (HTML view)
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])
            ->name('invoices.print');

        // Download invoice PDF
        Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])
            ->name('invoices.download');

        // Generate invoice PDF (preview)
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
            ->name('invoices.pdf');

        Route::post('/orders/{order}/invoice/generate', [InvoiceController::class, 'generate'])
            ->name('invoices.generate');


        // =========================
        // Bulk actions
        // =========================
        Route::post('invoices/bulk-delete', [InvoiceController::class, 'bulkDelete'])
            ->name('invoices.bulk-delete');

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
        

        Route::post('orders/bulk-status', [OrderController::class, 'bulkStatus'])
        ->name('orders.bulk-status');

        // Bulk delete orders
        Route::post('orders/bulk-delete', [OrderController::class, 'bulkDelete'])
            ->name('orders.bulk-delete');

        Route::put('/orders/{order}/billing', [OrderController::class, 'updateBilling'])
        ->name('orders.billing.update');

        Route::post('/orders/{order}/notes', [OrderNoteController::class, 'store'])
            ->name('orders.notes.store');

        Route::delete('/orders/notes/{note}', [OrderNoteController::class, 'destroy'])
            ->name('orders.notes.destroy');

        Route::post('/orders/deliveries/{delivery}/retry', [OrderController::class, 'retryDelivery'])
            ->name('admin.deliveries.retry');

        Route::post('/orders/{order}/resend-notification', [OrderController::class, 'resendNotification'])
            ->name('orders.resend-notification');

        Route::get('/order-notification-settings', fn() => redirect()->route('settings.notifications'))
            ->name('order-notification-settings.index');

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
        Route::resource('sliders', SliderController::class)->except(['show']);
        Route::post('sliders/{slider}/toggle', [SliderController::class, 'toggleStatus'])->name('sliders.toggle');
        Route::post('sliders/reorder', [SliderController::class, 'reorder'])->name('sliders.reorder');
        Route::post('sliders/{slider}/duplicate', [SliderController::class, 'duplicate'])->name('sliders.duplicate');
        Route::delete('sliders/bulk-delete', [SliderController::class, 'bulkDelete'])->name('sliders.bulk-delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Website Management Routes (Home Page, Shop Page, Footer)
    |--------------------------------------------------------------------------
    */
    Route::prefix('website')->name('website.')->group(function () {
        Route::get('/homepage', fn() => redirect()->route('settings.website'))->name('homepage');
        Route::get('/shoppage', fn() => redirect()->route('settings.website'))->name('shoppage');
        Route::get('/footer', fn() => redirect()->route('settings.website'))->name('footer');
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

        // Menus
        Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
        Route::post('menus', [MenuController::class, 'store'])->name('menus.store');
        Route::get('menus/linkable-items', [MenuController::class, 'linkableItems'])->name('menus.linkable-items');
        Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
        Route::put('menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
        Route::delete('menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');

        // FAQs
        Route::get('faqs', [FaqController::class, 'index'])->name('faqs.index');
        Route::put('faqs', [FaqController::class, 'update'])->name('faqs.update');

        // Static Pages (About, Contact, Privacy, Terms)
        Route::get('static-pages/{page}', [StaticPageController::class, 'edit'])->name('static-pages.edit');
        Route::put('static-pages/{page}', [StaticPageController::class, 'update'])->name('static-pages.update');

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

            // Bulk delete blogs
            Route::delete('bulk-delete', [BlogController::class, 'bulkDelete'])
                ->name('bulk-delete');

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

            // Bulk approve blog comments
            Route::post('bulk-approve', [BlogCommentController::class, 'bulkApprove'])
                ->name('bulk-approve');

            // Bulk delete blog comments
            Route::post('bulk-delete', [BlogCommentController::class, 'bulkDelete'])
                ->name('bulk-delete');

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

         Route::get('wallet-settings', fn() => redirect()->route('settings.wallet'))
            ->name('wallet-settings.edit');

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



     Route::middleware('permission:settings')->prefix('settings')->name('settings.')->group(function () {

        Route::get('/', fn() => redirect()->route('settings.general'));

        // Platform
        Route::get('/general', [SettingController::class, 'general'])->name('general');
        Route::put('/general', [SettingController::class, 'updateGeneral'])->name('general.update');
        Route::get('/branding', [SettingController::class, 'branding'])->name('branding');
        Route::put('/branding', [SettingController::class, 'updateBranding'])->name('branding.update');
        Route::get('/security', [SettingController::class, 'security'])->name('security');
        Route::put('/security', [SettingController::class, 'updateSecurity'])->name('security.update');
        Route::get('/registration', [SettingController::class, 'registration'])->name('registration');
        Route::put('/registration', [SettingController::class, 'updateRegistration'])->name('registration.update');

        // Affiliate
        Route::get('/affiliate', [SettingController::class, 'affiliate'])->name('affiliate');
        Route::put('/affiliate', [SettingController::class, 'updateAffiliate'])->name('affiliate.update');

        // Commerce
        Route::get('/store', [SettingController::class, 'store'])->name('store');
        Route::put('/store', [SettingController::class, 'updateStore'])->name('store.update');
        Route::get('/checkout', [SettingController::class, 'checkout'])->name('checkout');
        Route::put('/checkout', [SettingController::class, 'updateCheckout'])->name('checkout.update');
        Route::get('/products', [SettingController::class, 'products'])->name('products');
        Route::put('/products', [SettingController::class, 'updateProducts'])->name('products.update');
        Route::get('/vendor', [SettingController::class, 'vendor'])->name('vendor');
        Route::put('/vendor', [SettingController::class, 'updateVendor'])->name('vendor.update');
        Route::get('/refund-escrow', [SettingController::class, 'refundEscrow'])->name('refund-escrow');
        Route::put('/refund-escrow', [SettingController::class, 'updateRefundEscrow'])->name('refund-escrow.update');
        Route::get('/reviews', [SettingController::class, 'reviews'])->name('reviews');
        Route::put('/reviews', [SettingController::class, 'updateReviews'])->name('reviews.update');

        // Financial
        Route::get('/wallet', [SettingController::class, 'wallet'])->name('wallet');
        Route::put('/wallet', [SettingController::class, 'updateWallet'])->name('wallet.update');
        Route::get('/invoice', [SettingController::class, 'invoice'])->name('invoice');
        Route::put('/invoice', [SettingController::class, 'updateInvoice'])->name('invoice.update');
        Route::get('/currency', [SettingController::class, 'currency'])->name('currency');
        Route::put('/currency', [SettingController::class, 'updateCurrency'])->name('currency.update');

        // Communication
        Route::get('/email', [SettingController::class, 'email'])->name('email');
        Route::put('/email', [SettingController::class, 'updateEmail'])->name('email.update');
        Route::get('/notifications', [SettingController::class, 'notifications'])->name('notifications');
        Route::put('/notifications/{type}', [SettingController::class, 'updateNotifications'])->name('notifications.update');

        // Content & Marketing
        Route::get('/seo', [SettingController::class, 'seo'])->name('seo');
        Route::put('/seo', [SettingController::class, 'updateSeo'])->name('seo.update');
        Route::get('/social', [SettingController::class, 'social'])->name('social');
        Route::put('/social', [SettingController::class, 'updateSocial'])->name('social.update');
        Route::get('/legal', [SettingController::class, 'legal'])->name('legal');
        Route::put('/legal', [SettingController::class, 'updateLegal'])->name('legal.update');
        Route::get('/website', [SettingController::class, 'website'])->name('website');
        Route::put('/website/{section}', [SettingController::class, 'updateWebsite'])->name('website.update');

        // System
        Route::get('/api-integrations', [SettingController::class, 'apiIntegrations'])->name('api-integrations');
        Route::put('/api-integrations', [SettingController::class, 'updateApiIntegrations'])->name('api-integrations.update');
        Route::get('/maintenance', [SettingController::class, 'maintenance'])->name('maintenance');
        Route::put('/maintenance', [SettingController::class, 'updateMaintenance'])->name('maintenance.update');
        Route::get('/ai', [SettingController::class, 'ai'])->name('ai');
        Route::put('/ai', [SettingController::class, 'updateAi'])->name('ai.update');

        Route::post('/ai/generate', [AiContentController::class, 'generate'])->name('ai.generate');

    });

    /*
    |--------------------------------------------------------------------------
    | Subscribers
    |--------------------------------------------------------------------------
    */
    Route::get('/subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
    Route::post('/subscribers', [SubscriberController::class, 'store'])->name('subscribers.store');
    Route::put('/subscribers/{subscriber}', [SubscriberController::class, 'update'])->name('subscribers.update');
    Route::delete('/subscribers/{subscriber}', [SubscriberController::class, 'destroy'])->name('subscribers.destroy');
    Route::post('/subscribers/bulk-delete', [SubscriberController::class, 'bulkDelete'])->name('subscribers.bulk-delete');
    Route::get('/subscribers/export', [SubscriberController::class, 'export'])->name('subscribers.export');

    /*
    |--------------------------------------------------------------------------
    | Contact Messages
    |--------------------------------------------------------------------------
    */
    Route::get('/contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('/contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::put('/contact-messages/{contactMessage}', [ContactMessageController::class, 'update'])->name('contact-messages.update');
    Route::delete('/contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');
    Route::post('/contact-messages/bulk-delete', [ContactMessageController::class, 'bulkDelete'])->name('contact-messages.bulk-delete');
    Route::post('/contact-messages/bulk-status', [ContactMessageController::class, 'bulkStatus'])->name('contact-messages.bulk-status');

    /*
    |--------------------------------------------------------------------------
    | Reports & Analytics
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:reports')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/sales', [ReportsController::class, 'salesData'])->name('sales');
        Route::get('/revenue', [ReportsController::class, 'revenueData'])->name('revenue');
        Route::get('/products', [ReportsController::class, 'productData'])->name('products');
        Route::get('/customers', [ReportsController::class, 'customerData'])->name('customers');
        Route::get('/sellers', [ReportsController::class, 'sellerData'])->name('sellers');
        Route::get('/payments', [ReportsController::class, 'paymentData'])->name('payments');
        Route::get('/refunds', [ReportsController::class, 'refundData'])->name('refunds');
        Route::get('/support', [ReportsController::class, 'supportData'])->name('support');

        Route::prefix('export')->name('export.')->group(function () {
            Route::get('/sales', [ReportExportController::class, 'sales'])->name('sales');
            Route::get('/revenue', [ReportExportController::class, 'revenue'])->name('revenue');
            Route::get('/products', [ReportExportController::class, 'products'])->name('products');
            Route::get('/customers', [ReportExportController::class, 'customers'])->name('customers');
            Route::get('/sellers', [ReportExportController::class, 'sellers'])->name('sellers');
            Route::get('/payments', [ReportExportController::class, 'payments'])->name('payments');
            Route::get('/refunds', [ReportExportController::class, 'refunds'])->name('refunds');
            Route::get('/support', [ReportExportController::class, 'support'])->name('support');
            Route::get('/full', [ReportExportController::class, 'full'])->name('full');
            Route::post('/async/{type}', [ReportExportController::class, 'queueExport'])->name('async');
            Route::get('/download/{filename}', [ReportExportController::class, 'downloadExport'])->name('download');
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Refund Management
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:orders')->prefix('refunds')->name('refunds.')->group(function () {
        Route::get('/', [RefundController::class, 'index'])->name('index');
        Route::get('/{id}', [RefundController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [RefundController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [RefundController::class, 'reject'])->name('reject');
        Route::post('/{id}/process', [RefundController::class, 'process'])->name('process');
        Route::post('/bulk-action', [RefundController::class, 'bulkAction'])->name('bulk-action');
    });



    /*
    |--------------------------------------------------------------------------
    | Notification Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/search-users', [NotificationController::class, 'searchUsers'])->name('search-users');
        Route::post('/send', [NotificationController::class, 'send'])->name('send');
        Route::post('/send-all', [NotificationController::class, 'sendToAll'])->name('send-all');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete', [NotificationController::class, 'bulkDelete'])->name('bulk-delete');
    });



    /*
    |--------------------------------------------------------------------------
    | Support Tickets
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:support-tickets')->group(function () {
        Route::get('/support-tickets', [SupportTicketController::class, 'index'])->name('support-tickets.index');
        Route::get('/support-tickets/create', [SupportTicketController::class, 'create'])->name('support-tickets.create');
        Route::post('/support-tickets', [SupportTicketController::class, 'store'])->name('support-tickets.store');
        Route::get('/support-tickets/{supportTicket}', [SupportTicketController::class, 'show'])->name('support-tickets.show');
        Route::post('/support-tickets/{supportTicket}/reply', [SupportTicketController::class, 'reply'])->name('support-tickets.reply');
        Route::put('/support-tickets/{supportTicket}/status', [SupportTicketController::class, 'updateStatus'])->name('support-tickets.update-status');
        Route::put('/support-tickets/{supportTicket}/assign', [SupportTicketController::class, 'assign'])->name('support-tickets.assign');
        Route::put('/support-tickets/{supportTicket}/priority', [SupportTicketController::class, 'updatePriority'])->name('support-tickets.update-priority');
        Route::post('/support-tickets/{supportTicket}/escalate', [SupportTicketController::class, 'escalate'])->name('support-tickets.escalate');
        Route::delete('/support-tickets/{supportTicket}', [SupportTicketController::class, 'destroy'])->name('support-tickets.destroy');
        Route::post('/support-tickets/bulk-delete', [SupportTicketController::class, 'bulkDelete'])->name('support-tickets.bulk-delete');
        Route::post('/support-tickets/bulk-action', [SupportTicketController::class, 'bulkAction'])->name('support-tickets.bulk-action');

        // Canned Responses
        Route::resource('canned-responses', CannedResponseController::class)->except(['create', 'edit']);

        // Ticket Departments
        Route::resource('ticket-departments', TicketDepartmentController::class)->except(['create', 'edit']);

        // Ticket Notification Settings
        Route::get('/ticket-notification-settings', fn() => redirect()->route('settings.notifications'))
            ->name('ticket-notification-settings.index');
        Route::put('/ticket-notification-settings', [\App\Http\Controllers\SettingController::class, 'updateNotifications'])
            ->defaults('type', 'ticket')
            ->name('ticket-notification-settings.update');
    });


    /*
    |--------------------------------------------------------------------------
    | Affiliate Management
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:affiliates')->group(function () {

        Route::get('affiliates', [AffiliateController::class, 'index'])->name('affiliates.index');
        Route::get('affiliates/pending', [AffiliateController::class, 'pending'])->name('affiliates.pending');
        Route::get('affiliates/{affiliate}', [AffiliateController::class, 'show'])->name('affiliates.show');
        Route::post('affiliates/{affiliate}/approve', [AffiliateController::class, 'approve'])->name('affiliates.approve');
        Route::post('affiliates/{affiliate}/reject', [AffiliateController::class, 'reject'])->name('affiliates.reject');
        Route::post('affiliates/{affiliate}/suspend', [AffiliateController::class, 'suspend'])->name('affiliates.suspend');
        Route::post('affiliates/{affiliate}/reactivate', [AffiliateController::class, 'reactivate'])->name('affiliates.reactivate');
        Route::put('affiliates/{affiliate}/tier', [AffiliateController::class, 'updateTier'])->name('affiliates.update-tier');
        Route::delete('affiliates/{affiliate}', [AffiliateController::class, 'destroy'])->name('affiliates.destroy');
        Route::post('affiliates/bulk-status', [AffiliateController::class, 'bulkStatus'])->name('affiliates.bulk-status');
        Route::post('affiliates/bulk-delete', [AffiliateController::class, 'bulkDelete'])->name('affiliates.bulk-delete');

        Route::get('affiliate-commissions', [AffiliateCommissionController::class, 'index'])->name('affiliate-commissions.index');
        Route::post('affiliate-commissions/{commission}/release', [AffiliateCommissionController::class, 'release'])->name('affiliate-commissions.release');
        Route::post('affiliate-commissions/{commission}/reverse', [AffiliateCommissionController::class, 'reverse'])->name('affiliate-commissions.reverse');

        Route::get('affiliate-withdrawals', [AffiliateWithdrawalController::class, 'index'])->name('affiliate-withdrawals.index');
        Route::get('affiliate-withdrawals/pending', [AffiliateWithdrawalController::class, 'pending'])->name('affiliate-withdrawals.pending');
        Route::post('affiliate-withdrawals/{withdrawal}/approve', [AffiliateWithdrawalController::class, 'approve'])->name('affiliate-withdrawals.approve');
        Route::post('affiliate-withdrawals/{withdrawal}/reject', [AffiliateWithdrawalController::class, 'reject'])->name('affiliate-withdrawals.reject');

        Route::get('affiliate-tiers', [AffiliateTierController::class, 'index'])->name('affiliate-tiers.index');
        Route::post('affiliate-tiers', [AffiliateTierController::class, 'store'])->name('affiliate-tiers.store');
        Route::put('affiliate-tiers/{affiliateTier}', [AffiliateTierController::class, 'update'])->name('affiliate-tiers.update');
        Route::delete('affiliate-tiers/{affiliateTier}', [AffiliateTierController::class, 'destroy'])->name('affiliate-tiers.destroy');
    });

});


require __DIR__.'/auth.php';
