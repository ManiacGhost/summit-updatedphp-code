<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductDetail;
use App\Models\ProductMain;
use Illuminate\Http\Request;

class ProductDetailController extends Controller
{
    /**
     * Display a listing of all product details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $details = ProductDetail::with('product', 'series', 'subcategory', 'material', 'warranty', 'certification')->get();

            return response()->json([
                'success' => true,
                'message' => 'Product details retrieved successfully',
                'data' => $details,
                'count' => $details->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving product details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product detail in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'nullable|string|max:50|exists:sm_products_main,product_id',
                'series_id' => 'nullable|string|max:50|exists:sm_series,series_id',
                'subcat_id' => 'nullable|string|max:50|exists:sm_subcategories,subcat_id',
                'material_id' => 'nullable|string|max:50|exists:sm_materials,material_id',
                'warranty_id' => 'nullable|string|max:50|exists:sm_warranty,warranty_id',
                'certification_id' => 'nullable|string|max:50|exists:sm_certifications,cert_id',
                'net_quantity' => 'nullable|string|max:50',
                'weight' => 'nullable|string|max:50',
                'mrp' => 'nullable|string|max:50',
                'contents' => 'nullable|string',
                'item_dimensions' => 'nullable|string|max:255',
                'package_dimensions' => 'nullable|string|max:255',
                'manufacturer' => 'nullable|string|max:255',
                'marketer' => 'nullable|string|max:255',
                'customer_care' => 'nullable|string',
                'description' => 'nullable|string',
                'image' => 'nullable|string|max:2000'
            ]);

            $detail = ProductDetail::create($validated);
            $detail = $detail->load('product', 'series', 'subcategory', 'material', 'warranty', 'certification');

            return response()->json([
                'success' => true,
                'message' => 'Product detail created successfully',
                'data' => $detail
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
                'message' => 'Error creating product detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product detail.
     *
     * @param  int  $detailId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($detailId)
    {
        try {
            $detail = ProductDetail::with('product', 'series', 'subcategory', 'material', 'warranty', 'certification')
                ->findOrFail($detailId);

            return response()->json([
                'success' => true,
                'message' => 'Product detail retrieved successfully',
                'data' => $detail
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product detail not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving product detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product detail in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $detailId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $detailId)
    {
        try {
            $detail = ProductDetail::findOrFail($detailId);

            $validated = $request->validate([
                'product_id' => 'nullable|string|max:50|exists:sm_products_main,product_id',
                'series_id' => 'nullable|string|max:50|exists:sm_series,series_id',
                'subcat_id' => 'nullable|string|max:50|exists:sm_subcategories,subcat_id',
                'material_id' => 'nullable|string|max:50|exists:sm_materials,material_id',
                'warranty_id' => 'nullable|string|max:50|exists:sm_warranty,warranty_id',
                'certification_id' => 'nullable|string|max:50|exists:sm_certifications,cert_id',
                'net_quantity' => 'nullable|string|max:50',
                'weight' => 'nullable|string|max:50',
                'mrp' => 'nullable|string|max:50',
                'contents' => 'nullable|string',
                'item_dimensions' => 'nullable|string|max:255',
                'package_dimensions' => 'nullable|string|max:255',
                'manufacturer' => 'nullable|string|max:255',
                'marketer' => 'nullable|string|max:255',
                'customer_care' => 'nullable|string',
                'description' => 'nullable|string',
                'image' => 'nullable|string|max:2000'
            ]);

            $detail->update($validated);
            $detail = $detail->load('product', 'series', 'subcategory', 'material', 'warranty', 'certification');

            return response()->json([
                'success' => true,
                'message' => 'Product detail updated successfully',
                'data' => $detail
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product detail not found'
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
                'message' => 'Error updating product detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product detail from storage.
     *
     * @param  int  $detailId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($detailId)
    {
        try {
            $detail = ProductDetail::findOrFail($detailId);
            $detail->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product detail deleted successfully',
                'data' => ['detail_id' => $detailId]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product detail not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting product detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all details for a specific product.
     *
     * @param  string  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByProduct($productId)
    {
        try {
            // Verify product exists
            ProductMain::where('product_id', $productId)->firstOrFail();

            $details = ProductDetail::with('product', 'series', 'subcategory', 'material', 'warranty', 'certification')
                ->where('product_id', $productId)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Product details retrieved successfully',
                'data' => $details,
                'count' => $details->count()
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving product details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search product details by various fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');
            $productId = $request->input('product_id');

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query must be at least 2 characters'
                ], 422);
            }

            $details = ProductDetail::with('product', 'series', 'subcategory', 'material', 'warranty', 'certification')
                ->where(function ($q) use ($query) {
                    $q->where('manufacturer', 'like', "%{$query}%")
                      ->orWhere('marketer', 'like', "%{$query}%")
                      ->orWhere('net_quantity', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%");
                });

            if ($productId) {
                $details->where('product_id', $productId);
            }

            $details = $details->get();

            return response()->json([
                'success' => true,
                'message' => 'Search completed successfully',
                'data' => $details,
                'count' => $details->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching product details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Filter product details by various criteria.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filter(Request $request)
    {
        try {
            $details = ProductDetail::with('product', 'series', 'subcategory', 'material', 'warranty', 'certification');

            if ($request->has('product_id') && $request->input('product_id')) {
                $details->where('product_id', $request->input('product_id'));
            }

            if ($request->has('series_id') && $request->input('series_id')) {
                $details->where('series_id', $request->input('series_id'));
            }

            if ($request->has('subcat_id') && $request->input('subcat_id')) {
                $details->where('subcat_id', $request->input('subcat_id'));
            }

            if ($request->has('material_id') && $request->input('material_id')) {
                $details->where('material_id', $request->input('material_id'));
            }

            if ($request->has('warranty_id') && $request->input('warranty_id')) {
                $details->where('warranty_id', $request->input('warranty_id'));
            }

            if ($request->has('manufacturer') && $request->input('manufacturer')) {
                $details->where('manufacturer', 'like', '%' . $request->input('manufacturer') . '%');
            }

            $details = $details->get();

            return response()->json([
                'success' => true,
                'message' => 'Filtering completed successfully',
                'data' => $details,
                'count' => $details->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering product details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
