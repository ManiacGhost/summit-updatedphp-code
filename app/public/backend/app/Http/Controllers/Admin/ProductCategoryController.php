<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    /**
     * Generate next available category ID
     *
     * @return string
     */
    private function generateNextCategoryId()
    {
        $lastCategory = ProductCategory::orderBy('category_id', 'desc')->first();
        if (!$lastCategory) {
            return 'CAT001';
        }
        
        // Extract numeric part and increment
        $lastId = $lastCategory->category_id;
        if (preg_match('/(\d+)$/', $lastId, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
            return 'CAT' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
        
        return 'CAT001';
    }

    /**
     * Display a listing of all product categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $categories = ProductCategory::orderBy('sort_order', 'asc')
                ->orderBy('category_name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories,
                'count' => $categories->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get next available category ID
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNextCategoryId()
    {
        try {
            $nextId = $this->generateNextCategoryId();
            
            return response()->json([
                'success' => true,
                'message' => 'Next category ID generated successfully',
                'data' => ['next_category_id' => $nextId]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating next category ID',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Auto-generate category_id if not provided
            $requestData = $request->all();
            if (empty($requestData['category_id'])) {
                $requestData['category_id'] = $this->generateNextCategoryId();
                $request->merge(['category_id' => $requestData['category_id']]);
            }

            $validated = $request->validate([
                'category_id' => 'required|string|max:20|unique:sm_product_categories,category_id',
                'category_name' => 'required|string|max:100',
                'sort_order' => 'nullable|integer|min:0'
            ]);

            $category = ProductCategory::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'request_data' => $request->all() // Debug: include request data
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product category.
     *
     * @param  string  $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($categoryId)
    {
        try {
            $category = ProductCategory::where('category_id', $categoryId)->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data' => $category
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $categoryId)
    {
        try {
            $category = ProductCategory::where('category_id', $categoryId)->firstOrFail();

            $validated = $request->validate([
                'category_name' => 'sometimes|required|string|max:100',
                'sort_order' => 'sometimes|nullable|integer|min:0'
            ]);

            $category->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
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
                'message' => 'Error updating category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product category from storage.
     *
     * @param  string  $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($categoryId)
    {
        try {
            $category = ProductCategory::where('category_id', $categoryId)->firstOrFail();
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
                'data' => ['category_id' => $categoryId]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update sort order for multiple categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSortOrder(Request $request)
    {
        try {
            $validated = $request->validate([
                'categories' => 'required|array',
                'categories.*.category_id' => 'required|string',
                'categories.*.sort_order' => 'required|integer|min:0'
            ]);

            foreach ($validated['categories'] as $item) {
                ProductCategory::where('category_id', $item['category_id'])
                    ->update(['sort_order' => $item['sort_order']]);
            }

            $updatedCategories = ProductCategory::whereIn(
                'category_id',
                collect($validated['categories'])->pluck('category_id')
            )->get();

            return response()->json([
                'success' => true,
                'message' => 'Sort order updated successfully',
                'data' => $updatedCategories
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating sort order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
