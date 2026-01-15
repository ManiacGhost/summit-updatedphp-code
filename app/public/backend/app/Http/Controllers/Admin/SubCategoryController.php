<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of all subcategories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $subcategories = SubCategory::all();

            return response()->json([
                'success' => true,
                'message' => 'Subcategories retrieved successfully',
                'data' => $subcategories,
                'count' => $subcategories->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving subcategories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created subcategory in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'subcat_id' => 'required|string|max:50|unique:sm_subcategories,subcat_id',
                'product_id' => 'nullable|string|max:50',
                'subcat_name' => 'nullable|string|max:255'
            ]);

            $subcategory = SubCategory::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Subcategory created successfully',
                'data' => $subcategory
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
                'message' => 'Error creating subcategory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified subcategory.
     *
     * @param  string  $subcatId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($subcatId)
    {
        try {
            $subcategory = SubCategory::where('subcat_id', $subcatId)->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Subcategory retrieved successfully',
                'data' => $subcategory
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving subcategory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified subcategory in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $subcatId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $subcatId)
    {
        try {
            $subcategory = SubCategory::where('subcat_id', $subcatId)->firstOrFail();

            $validated = $request->validate([
                'product_id' => 'nullable|string|max:50',
                'subcat_name' => 'nullable|string|max:255'
            ]);

            $subcategory->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Subcategory updated successfully',
                'data' => $subcategory
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
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
                'message' => 'Error updating subcategory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified subcategory from storage.
     *
     * @param  string  $subcatId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($subcatId)
    {
        try {
            $subcategory = SubCategory::where('subcat_id', $subcatId)->firstOrFail();
            $subcategory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subcategory deleted successfully',
                'data' => ['subcat_id' => $subcatId]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting subcategory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all subcategories for a specific product.
     *
     * @param  string  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByProduct($productId)
    {
        try {
            $subcategories = SubCategory::where('product_id', $productId)->get();

            return response()->json([
                'success' => true,
                'message' => 'Subcategories retrieved successfully',
                'data' => $subcategories,
                'count' => $subcategories->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving subcategories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
