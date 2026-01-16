<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\TaxonomyController;

Route::prefix('v1')->group(function () {

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
            Route::get('/me', [AuthController::class, 'me']);
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
    | Taxonomies
    |--------------------------------------------------------------------------
    */
    Route::prefix('taxonomies')->group(function () {
        Route::get('/categories', [TaxonomyController::class, 'categories']);
        Route::get('/platforms', [TaxonomyController::class, 'platforms']);
        Route::get('/types', [TaxonomyController::class, 'types']);
        Route::get('/regions', [TaxonomyController::class, 'regions']);
        Route::get('/languages', [TaxonomyController::class, 'languages']);
        Route::get('/works-on', [TaxonomyController::class, 'worksOn']);
        Route::get('/developers', [TaxonomyController::class, 'developers']);
        Route::get('/publishers', [TaxonomyController::class, 'publishers']);
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
    | Orders (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::put('/orders/{id}/pay', [OrderController::class, 'markAsPaid']);
        Route::post('/orders/{id}/refund', [OrderController::class, 'refund']);
        Route::post('/orders/{id}/notes', [OrderController::class, 'addNote']);
    });

});
