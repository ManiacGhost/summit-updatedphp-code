<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductMain;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductMainController extends Controller
{
    /**
     * Display a listing of all main products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $products = ProductMain::with('category')->get();

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => $products,
                'count' => $products->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|string|max:50|unique:sm_products_main,product_id',
                'category_id' => 'nullable|string|max:20|exists:sm_product_categories,category_id',
                'product_name' => 'nullable|string|max:255',
                'hsn_code' => 'nullable|string|max:20',
                'tax_rate' => 'nullable|numeric|min:0|max:999.99',
                'trending_flag' => 'nullable|boolean'
            ]);

            $product = ProductMain::create($validated);
            $product = $product->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product.
     *
     * @param  string  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($productId)
    {
        try {
            $product = ProductMain::with('category')->where('product_id', $productId)->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => $product
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $productId)
    {
        try {
            $product = ProductMain::where('product_id', $productId)->firstOrFail();

            $validated = $request->validate([
                'category_id' => 'nullable|string|max:20|exists:sm_product_categories,category_id',
                'product_name' => 'nullable|string|max:255',
                'hsn_code' => 'nullable|string|max:20',
                'tax_rate' => 'nullable|numeric|min:0|max:999.99',
                'trending_flag' => 'nullable|boolean'
            ]);

            $product->update($validated);
            $product = $product->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  string  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($productId)
    {
        try {
            $product = ProductMain::where('product_id', $productId)->firstOrFail();
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
                'data' => ['product_id' => $productId]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all products for a specific category.
     *
     * @param  string  $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCategory($categoryId)
    {
        try {
            // Verify category exists
            ProductCategory::where('category_id', $categoryId)->firstOrFail();

            $products = ProductMain::with('category')
                ->where('category_id', $categoryId)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => $products,
                'count' => $products->count()
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search products by name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query must be at least 2 characters'
                ], 422);
            }

            $products = ProductMain::with('category')
                ->where('product_name', 'like', "%{$query}%")
                ->orWhere('product_id', 'like', "%{$query}%")
                ->orWhere('hsn_code', 'like', "%{$query}%")
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Search completed successfully',
                'data' => $products,
                'count' => $products->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all trending products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTrending()
    {
        try {
            $products = ProductMain::with('category')
                ->where('trending_flag', 1)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Trending products retrieved successfully',
                'data' => $products,
                'count' => $products->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving trending products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle trending flag for a product.
     *
     * @param  string  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleTrending($productId)
    {
        try {
            $product = ProductMain::where('product_id', $productId)->firstOrFail();
            $product->trending_flag = !$product->trending_flag;
            $product->save();
            $product = $product->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Trending flag toggled successfully',
                'data' => $product
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling trending flag',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
