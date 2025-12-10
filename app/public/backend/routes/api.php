<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\CartController;


// Route::get('/', function () {
//     return "hii API";
// });


/* ------------------------Products APIs-------------------------*/


/* ------------------------Category APIs-------------------------*/

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);


/* ------------------------Products APIs-------------------------*/
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);


/* ------------------------Attribute APIs-------------------------*/
Route::get('/attributes', [AttributeController::class, 'index']);


/* ------------------------Variants APIs-------------------------*/
Route::get('/variants/{id}', [ProductVariantController::class, 'show']);
Route::post('/variants', [ProductVariantController::class, 'store']);






/*  --------------------------Cart Route------------------------------ */

Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart/add', [CartController::class, 'add']);
Route::get('/cart/remove/{item}', [CartController::class, 'remove']);
Route::post('/cart/update/{item}', [CartController::class, 'updateQuantity']);
Route::post('/cart/clear', [CartController::class, 'clear']);


