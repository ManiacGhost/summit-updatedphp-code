<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\Api\ProductViewController;
use App\Http\Controllers\Api\MegaMenuController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GlobalSearchController;


// Route::get('/', function () {
//     return "hii API";
// });


/* -------------------------Authentication APIs------------------------*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('jwt');
Route::get('/me', [AuthController::class, 'me'])->middleware('jwt');


/* ------------------------Products APIs-------------------------*/


/* ------------------------Category APIs-------------------------*/

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/mega-menu', [MegaMenuController::class, 'index']);


/* ------------------------Global Search APIs------------------------*/
Route::get('/search', [GlobalSearchController::class, 'search']);
Route::get('/search/suggestions', [GlobalSearchController::class, 'suggestions']);
Route::get('/search/advanced', [GlobalSearchController::class, 'advancedSearch']);


/* ------------------------Products APIs-------------------------*/
// Flat/view-backed product APIs (query vw_product_full_view) - MUST come first (more specific routes)
Route::get('/products/view', [ProductViewController::class, 'index']);
Route::get('/products/view/{id}', [ProductViewController::class, 'show']);

// Admin product APIs
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);


/* ------------------------Attribute APIs-------------------------*/
Route::get('/attributes', [AttributeController::class, 'index']);


/* ------------------------Variants APIs-------------------------*/
Route::get('/variants/{id}', [ProductVariantController::class, 'show']);
Route::post('/variants', [ProductVariantController::class, 'store']);






/*  --------------------------Cart Route------------------------------ */

Route::middleware('jwt')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::get('/cart/remove/{item}', [CartController::class, 'remove']);
    Route::post('/cart/update/{item}', [CartController::class, 'updateQuantity']);
    Route::post('/cart/clear', [CartController::class, 'clear']);
});


