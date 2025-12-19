<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Services\S3PresignedUrlService;

class ProductViewController extends Controller
{
    /**
     * Convert S3 image URL to presigned URL
     */
    private function convertImageToPresignedUrl($product)
    {
        if (is_object($product)) {
            if (isset($product->image) && $product->image) {
                $product->image = $this->getPresignedUrl($product->image);
            }
        } elseif (is_array($product)) {
            if (isset($product['image']) && $product['image']) {
                $product['image'] = $this->getPresignedUrl($product['image']);
            }
        }
        return $product;
    }

    /**
     * Convert images in a collection of products
     */
    private function convertImagesToPresignedUrls($products)
    {
        if ($products instanceof \Illuminate\Pagination\Paginator || 
            $products instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return $products->through(fn($product) => $this->convertImageToPresignedUrl($product));
        }

        if (is_array($products)) {
            return array_map(fn($product) => $this->convertImageToPresignedUrl($product), $products);
        }

        return $products;
    }

    /**
     * Generate presigned URL from S3 image URL
     */
    private function getPresignedUrl($imageUrl)
    {
        if (!$imageUrl || strpos($imageUrl, 'X-Amz-Signature') !== false) {
            return $imageUrl; // Return if empty or already presigned
        }

        try {
            $svc = new S3PresignedUrlService();
            return $svc->convertToPresignedUrl($imageUrl, 60);
        } catch (\Exception $e) {
            \Log::error('Presigned URL service error: ' . $e->getMessage());
            return $imageUrl;
        }
    }
    /**
     * Return products from the vw_product_full_view with dynamic filters.
     * Supports: search, filter by columns, min_mrp, max_mrp, sort, order, pagination
     */
    public function index(Request $request)
    {
        $qb = DB::table('vw_product_full_view');

        // Search across common text fields
        if ($request->filled('search')) {
            $s = $request->search;
            $qb->where(function ($q) use ($s) {
                $q->where('product_name', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('manufacturer', 'like', "%{$s}%")
                  ->orWhere('master_category', 'like', "%{$s}%")
                  ->orWhere('subcat_name', 'like', "%{$s}%");
            });
        }

        // Allowed exact / IN filters
        $filters = [
            'product_id' => 'product_id',
            'category_id' => 'category_id',
            'master_category' => 'master_category',
            'subcat_name' => 'subcat_name',
            'series_name' => 'series_name',
            'material_name' => 'material_name',
            'warranty_text' => 'warranty_text',
            'certification' => 'certification',
            'net_quantity' => 'net_quantity'
        ];

        foreach ($filters as $param => $col) {
            if ($request->filled($param)) {
                $val = $request->get($param);
                // support comma-separated lists
                if (strpos($val, ',') !== false) {
                    $items = array_map('trim', explode(',', $val));
                    $qb->whereIn($col, $items);
                } else {
                    $qb->where($col, $val);
                }
            }
        }

        // Numeric range filters for mrp and weight
        if ($request->filled('min_mrp')) {
            $qb->whereRaw('CAST(mrp AS DECIMAL(12,2)) >= ?', [(float)$request->min_mrp]);
        }
        if ($request->filled('max_mrp')) {
            $qb->whereRaw('CAST(mrp AS DECIMAL(12,2)) <= ?', [(float)$request->max_mrp]);
        }

        if ($request->filled('min_weight')) {
            $qb->whereRaw('CAST(weight AS DECIMAL(12,3)) >= ?', [(float)$request->min_weight]);
        }
        if ($request->filled('max_weight')) {
            $qb->whereRaw('CAST(weight AS DECIMAL(12,3)) <= ?', [(float)$request->max_weight]);
        }

        // Sorting
        $allowedSorts = [
            'product_name', 'mrp', 'weight', 'master_category', 'series_name'
        ];
        $sort = $request->get('sort', 'product_name');
        $order = strtolower($request->get('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (in_array($sort, $allowedSorts)) {
            // If sorting by mrp/weight which are stored as text, cast when ordering
            if (in_array($sort, ['mrp', 'weight'])) {
                $qb->orderByRaw("CAST({$sort} AS DECIMAL(12,3)) {$order}");
            } else {
                $qb->orderBy($sort, $order);
            }
        }

        // Pagination
        $perPage = min((int)$request->get('per_page', 12), 200);
        $results = $qb->paginate($perPage);

        // Convert images to presigned URLs
        $results = $this->convertImagesToPresignedUrls($results);

        return response()->json($results);
    }

    /**
     * Return single product row by detail_id or product_id
     */
    public function show($id)
    {
        $row = DB::table('vw_product_full_view')
            ->where('detail_id', $id)
            ->orWhere('product_id', $id)
            ->first();

        if (! $row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // Convert image to presigned URL
        $row = $this->convertImageToPresignedUrl($row);

        return response()->json($row);
    }
}
