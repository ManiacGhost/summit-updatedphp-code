<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\SeriesController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\WarrantyController;
use App\Http\Controllers\Admin\CertificationController;
use App\Http\Controllers\Admin\ProductMainController;
use App\Http\Controllers\Admin\ProductDetailController;
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

// Product Categories (Admin CRUD)
Route::prefix('/admin/categories')->group(function () {
    Route::get('/', [ProductCategoryController::class, 'index']);
    Route::post('/', [ProductCategoryController::class, 'store']);
    Route::get('/next-id', [ProductCategoryController::class, 'getNextCategoryId']);
    Route::get('/{categoryId}', [ProductCategoryController::class, 'show']);
    Route::post('/{categoryId}', [ProductCategoryController::class, 'update']);
    Route::delete('/{categoryId}', [ProductCategoryController::class, 'destroy']);
    Route::post('/sort-order/update', [ProductCategoryController::class, 'updateSortOrder']);
});

// Subcategories (Admin CRUD)
Route::prefix('/admin/subcategories')->group(function () {
    Route::get('/', [SubCategoryController::class, 'index']);
    Route::post('/', [SubCategoryController::class, 'store']);
    Route::get('/{subcatId}', [SubCategoryController::class, 'show']);
    Route::post('/{subcatId}', [SubCategoryController::class, 'update']);
    Route::delete('/{subcatId}', [SubCategoryController::class, 'destroy']);
    Route::get('/product/{productId}', [SubCategoryController::class, 'getByProduct']);
});

// Series (Admin CRUD)
Route::prefix('/admin/series')->group(function () {
    Route::get('/', [SeriesController::class, 'index']);
    Route::post('/', [SeriesController::class, 'store']);
    Route::get('/{seriesId}', [SeriesController::class, 'show']);
    Route::post('/{seriesId}', [SeriesController::class, 'update']);
    Route::delete('/{seriesId}', [SeriesController::class, 'destroy']);
    Route::get('/product/{productId}', [SeriesController::class, 'getByProduct']);
});

// Materials (Admin CRUD)
Route::prefix('/admin/materials')->group(function () {
    Route::get('/', [MaterialController::class, 'index']);
    Route::post('/', [MaterialController::class, 'store']);
    Route::get('/{materialId}', [MaterialController::class, 'show']);
    Route::post('/{materialId}', [MaterialController::class, 'update']);
    Route::delete('/{materialId}', [MaterialController::class, 'destroy']);
});

// Warranty (Admin CRUD)
Route::prefix('/admin/warranty')->group(function () {
    Route::get('/', [WarrantyController::class, 'index']);
    Route::post('/', [WarrantyController::class, 'store']);
    Route::get('/{warrantyId}', [WarrantyController::class, 'show']);
    Route::post('/{warrantyId}', [WarrantyController::class, 'update']);
    Route::delete('/{warrantyId}', [WarrantyController::class, 'destroy']);
});

// Certifications (Admin CRUD)
Route::prefix('/admin/certifications')->group(function () {
    Route::get('/', [CertificationController::class, 'index']);
    Route::post('/', [CertificationController::class, 'store']);
    Route::get('/{certId}', [CertificationController::class, 'show']);
    Route::post('/{certId}', [CertificationController::class, 'update']);
    Route::delete('/{certId}', [CertificationController::class, 'destroy']);
});

// Main Products (Admin CRUD)
Route::prefix('/admin/products-main')->group(function () {
    Route::get('/', [ProductMainController::class, 'index']);
    Route::post('/', [ProductMainController::class, 'store']);
    Route::get('/search', [ProductMainController::class, 'search']);
    Route::get('/trending', [ProductMainController::class, 'getTrending']);
    Route::get('/{productId}', [ProductMainController::class, 'show']);
    Route::post('/{productId}', [ProductMainController::class, 'update']);
    Route::post('/{productId}/toggle-trending', [ProductMainController::class, 'toggleTrending']);
    Route::delete('/{productId}', [ProductMainController::class, 'destroy']);
    Route::get('/category/{categoryId}', [ProductMainController::class, 'getByCategory']);
});

// Product Details (Admin CRUD)
Route::prefix('/admin/product-details')->group(function () {
    Route::get('/', [ProductDetailController::class, 'index']);
    Route::post('/', [ProductDetailController::class, 'store']);
    Route::get('/search', [ProductDetailController::class, 'search']);
    Route::get('/filter', [ProductDetailController::class, 'filter']);
    Route::get('/{detailId}', [ProductDetailController::class, 'show']);
    Route::post('/{detailId}', [ProductDetailController::class, 'update']);
    Route::delete('/{detailId}', [ProductDetailController::class, 'destroy']);
    Route::get('/product/{productId}', [ProductDetailController::class, 'getByProduct']);
});

// Legacy Category APIs (Frontend)
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
Route::get('/products/view/trending', [ProductViewController::class, 'trending']);
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


