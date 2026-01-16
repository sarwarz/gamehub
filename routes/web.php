<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\SellerOfferController;
use App\Http\Controllers\ProductRegionController;
use App\Http\Controllers\ProductWorksOnController;
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
    Route::resource('products', ProductController::class)->middleware('permission:products');
    Route::delete('products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete')->middleware('permission:products');
    Route::get('/products/{id}/preview', [ProductController::class, 'preview'])->name('products.preview')->middleware('permission:products');
    Route::get('/products/{product}/offers', [ProductController::class, 'offers'])->name('products.offers')->middleware('permission:products');


    // Sellers
    Route::resource('sellers', SellerController::class)->middleware('permission:sellers');
    Route::delete('sellers/bulk-delete', [SellerController::class, 'bulkDelete'])->name('sellers.bulk-delete')->middleware('permission:sellers');

    // Seller Offers
    Route::resource('seller-offers', SellerOfferController::class)->middleware('permission:seller-offers');
    Route::delete('seller-offers/bulk-delete', [SellerOfferController::class, 'bulkDelete'])->name('seller-offers.bulk-delete')->middleware('permission:seller-offers');

    // Currencies
    Route::resource('currencies', CurrencyController::class)->middleware('permission:currencies');
    Route::delete('currencies/bulk-delete', [CurrencyController::class, 'bulkDelete'])->name('currencies.bulk-delete')->middleware('permission:currencies');
    Route::post('/currencies/update-rates', [CurrencyController::class, 'updateRates'])->name('currencies.updateRates')->middleware('permission:currencies');

    // Orders
    Route::resource('orders', OrderController::class)->middleware('permission:orders');
    Route::delete('orders/bulk-delete', [OrderController::class, 'bulkDelete'])->name('orders.bulk-delete')->middleware('permission:orders');

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
