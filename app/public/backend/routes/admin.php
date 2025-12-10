<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\ProductController;
// use App\Http\Controllers\Api\CategoryController;
// use App\Http\Controllers\Api\AttributeController;
// use App\Http\Controllers\Api\ProductVariantController;


Route::get('/dashboard', function () {
    return view('admin.dashboard');
});





// ////////////Products APIs
// Route::get('/categories', [CategoryController::class, 'index']);
// Route::get('/categories/{slug}', [CategoryController::class, 'show']);

// Route::get('/products', [ProductController::class, 'index']);
// Route::get('/products/{slug}', [ProductController::class, 'show']);
// Route::post('/products', [ProductController::class, 'store']);

// Route::get('/attributes', [AttributeController::class, 'index']);

// Route::get('/variants/{id}', [ProductVariantController::class, 'show']);
// Route::post('/variants', [ProductVariantController::class, 'store']);









