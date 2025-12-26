<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GlobalSearchController extends Controller
{
    /**
     * Generate presigned URL for S3 image
     */
    private function getPresignedImageUrl($imagePath)
    {
        if (!$imagePath) {
            return null;
        }

        try {
            return Storage::disk('s3')->temporaryUrl(
                $imagePath,
                now()->addHours(1)
            );
        } catch (\Exception $e) {
            \Log::error('Error generating presigned URL: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Global fuzzy search across products, variants, and product IDs
     */
    public function search(Request $request)
    {
        try {
            $search = trim($request->get('search', ''));
            
            if (strlen($search) < 2) {
                return response()->json([
                    'message' => 'Search term must be at least 2 characters',
                    'data' => []
                ], 400);
            }

            $searchPattern = "%{$search}%";
            $searchNumeric = is_numeric($search) ? (int)$search : null;

            // Query the denormalized view
            $rows = DB::table('vw_product_full_view')
                ->where(function ($q) use ($search, $searchPattern, $searchNumeric) {
                    $q->whereRaw("LOWER(TRIM(product_name)) LIKE LOWER(?)", [$searchPattern])
                        ->orWhereRaw("LOWER(TRIM(product_id)) LIKE LOWER(?)", [$searchPattern])
                        ->orWhereRaw("LOWER(TRIM(master_category)) LIKE LOWER(?)", [$searchPattern])
                        ->orWhereRaw("LOWER(TRIM(subcat_name)) LIKE LOWER(?)", [$searchPattern])
                        ->orWhereRaw("LOWER(TRIM(series_name)) LIKE LOWER(?)", [$searchPattern])
                        ->orWhereRaw("LOWER(TRIM(material_name)) LIKE LOWER(?)", [$searchPattern]);
                    
                    if ($searchNumeric !== null) {
                        $q->orWhere('product_id', $searchNumeric)
                            ->orWhere('detail_id', $searchNumeric);
                    }
                })
                ->orderBy('product_name')
                ->limit(100)
                ->get();

            // Group by product_id to get unique products with all variants
            $productsData = [];
            foreach ($rows as $row) {
                $productId = $row->product_id;
                
                if (!isset($productsData[$productId])) {
                    $productsData[$productId] = [
                        'id' => $row->product_id,
                        'name' => $row->product_name,
                        'image' => $this->getPresignedImageUrl($row->image),
                        'category' => [
                            'name' => $row->master_category ?? 'N/A',
                            'subcategory' => $row->subcat_name ?? 'N/A'
                        ],
                        'variants' => []
                    ];
                }
                
                // Add variant info
                $productsData[$productId]['variants'][] = [
                    'detail_id' => $row->detail_id,
                    'sku' => $row->hsn_code,
                    'image' => $this->getPresignedImageUrl($row->image),
                    'mrp' => $row->mrp ?? 0,
                    'tax_rate' => $row->tax_rate ?? 0,
                    'weight' => $row->weight ?? 0,
                    'series' => $row->series_name ?? 'N/A',
                    'material' => $row->material_name ?? 'N/A',
                    'warranty' => $row->warranty_text ?? 'N/A',
                    'certification' => $row->certification ?? 'N/A'
                ];
            }

            $results = array_values($productsData);
            
            // Add variant count to each product
            foreach ($results as &$product) {
                $product['variant_count'] = count($product['variants']);
            }

            return response()->json([
                'success' => true,
                'search_term' => $search,
                'data' => $results,
                'total' => count($results)
            ]);

        } catch (\Exception $e) {
            \Log::error('Global search error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Search error: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Autocomplete suggestions for search
     */
    public function suggestions(Request $request)
    {
        try {
            $search = trim($request->get('q', ''));

            if (strlen($search) < 2) {
                return response()->json([
                    'suggestions' => []
                ]);
            }

            $searchPattern = "%{$search}%";

            // Get distinct product names
            $productNames = DB::table('vw_product_full_view')
                ->whereRaw("LOWER(product_name) LIKE LOWER(?)", [$searchPattern])
                ->select(DB::raw("DISTINCT product_name as suggestion"), DB::raw("'product' as type"))
                ->limit(10)
                ->get();

            // Get distinct SKUs
            $skus = DB::table('vw_product_full_view')
                ->whereRaw("LOWER(TRIM(product_id)) LIKE LOWER(?)", [$searchPattern])
                ->select(DB::raw("DISTINCT product_id as suggestion"), DB::raw("'sku' as type"))
                ->limit(10)
                ->get();

            // Get distinct categories
            $categories = DB::table('vw_product_full_view')
                ->whereRaw("LOWER(master_category) LIKE LOWER(?)", [$searchPattern])
                ->select(DB::raw("DISTINCT master_category as suggestion"), DB::raw("'category' as type"))
                ->limit(5)
                ->get();

            $suggestions = $productNames->concat($skus)->concat($categories)
                ->unique('suggestion')
                ->values()
                ->all();

            return response()->json([
                'suggestions' => array_map(function ($item) {
                    return [
                        'text' => $item->suggestion,
                        'type' => $item->type
                    ];
                }, $suggestions)
            ]);
        } catch (\Exception $e) {
            \Log::error('Suggestions error: ' . $e->getMessage());
            return response()->json([
                'suggestions' => []
            ]);
        }
    }

    /**
     * Advanced search with filters
     */
    public function advancedSearch(Request $request)
    {
        try {
            $search = trim($request->get('search', ''));
            
            if (strlen($search) < 2) {
                return response()->json([
                    'message' => 'Search term must be at least 2 characters',
                    'data' => []
                ], 400);
            }

            $query = DB::table('vw_product_full_view')
                ->where(function ($q) use ($search) {
                    $searchPattern = "%{$search}%";
                    $q->whereRaw("LOWER(TRIM(product_name)) LIKE LOWER(?)", [$searchPattern])
                        ->orWhereRaw("LOWER(TRIM(product_id)) LIKE LOWER(?)", [$searchPattern])
                        ->orWhereRaw("LOWER(TRIM(master_category)) LIKE LOWER(?)", [$searchPattern])
                        ->orWhereRaw("LOWER(TRIM(series_name)) LIKE LOWER(?)", [$searchPattern]);
                });

            // Apply category filter if provided
            if ($request->filled('master_category')) {
                $query->where('master_category', $request->master_category);
            }

            // Apply material filter if provided
            if ($request->filled('material_name')) {
                $query->where('material_name', $request->material_name);
            }

            // Apply price range filter if provided
            if ($request->filled('min_mrp')) {
                $query->where('mrp', '>=', (float)$request->min_mrp);
            }
            if ($request->filled('max_mrp')) {
                $query->where('mrp', '<=', (float)$request->max_mrp);
            }

            // Pagination
            $perPage = min((int)$request->get('per_page', 20), 100);
            $results = $query->select(
                'product_id',
                'detail_id',
                'product_name',
                'image',
                'hsn_code',
                'mrp',
                'tax_rate',
                'master_category',
                'series_name',
                'material_name',
                'warranty_text',
                'certification'
            )
                ->distinct('product_id')
                ->paginate($perPage);

            // Transform results
            $data = $results->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'detail_id' => $item->detail_id,
                    'product_name' => $item->product_name,
                    'sku' => $item->hsn_code,
                    'mrp' => $item->mrp,
                    'tax_rate' => $item->tax_rate,
                    'category' => $item->master_category,
                    'series' => $item->series_name,
                    'material' => $item->material_name,
                    'warranty' => $item->warranty_text,
                    'certification' => $item->certification
                ];
            });

            return response()->json([
                'success' => true,
                'search_term' => $search,
                'data' => $data,
                'total' => $results->total(),
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'total_pages' => $results->lastPage(),
                    'per_page' => $results->perPage()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Advanced search error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Search error: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
