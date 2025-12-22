<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Services\S3PresignedUrlService;
use GuzzleHttp\Client;
use Aws\S3\S3Client;

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
     * Flow: 
     * 1. Fetch product from DB
     * 2. Extract bucket from image URL
     * 3. List all objects in the image directory using AWS SDK
     * 4. Generate presigned URLs for each object
     */
    public function show($id)
    {
        // Step 1: Fetch product from DB
        $row = DB::table('vw_product_full_view')
            ->where('detail_id', $id)
            ->orWhere('product_id', $id)
            ->first();

        if (! $row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // Convert primary image to presigned URL
        $row = $this->convertImageToPresignedUrl($row);

        // Step 2-4: Extract directory from image URL and list S3 objects in that directory
        $images = [];
        try {
            if (!empty($row->image)) {
                // Parse the image URL
                $parsed = parse_url($row->image);
                $host = $parsed['host'] ?? null;
                $path = $parsed['path'] ?? '';

                if ($host && $path) {
                    // Extract bucket from virtual-hosted-style URL (bucket.s3.region.amazonaws.com)
                    $bucketFromHost = null;
                    if (preg_match('/^([^.]+)\.s3/', $host, $m)) {
                        $bucketFromHost = $m[1];
                    }

                    if ($bucketFromHost) {
                        // Decode the object key to handle percent-encoded characters
                        $objectKey = ltrim($path, '/');
                        $decodedKey = rawurldecode($objectKey);

                        // Get the directory containing this object (without filename)
                        $directory = rtrim(dirname($decodedKey), '/');

                        \Log::info("S3 Image Listing", [
                            'bucket' => $bucketFromHost,
                            'decoded_key' => $decodedKey,
                            'directory' => $directory,
                            'listing_prefix' => $directory . '/'
                        ]);

                        // Use AWS SDK to list objects in the directory
                        $config = [
                            'version' => 'latest',
                            'region'  => env('AWS_DEFAULT_REGION', 'ap-southeast-2'),
                            'http' => [
                                'verify' => false  // Disable SSL verification for development
                            ]
                        ];

                        $accessKey = env('AWS_ACCESS_KEY_ID');
                        $secret = env('AWS_SECRET_ACCESS_KEY');
                        
                        if ($accessKey && $secret) {
                            $config['credentials'] = [
                                'key'    => $accessKey,
                                'secret' => $secret,
                            ];
                            \Log::info("AWS Credentials provided");
                        } else {
                            \Log::warning("AWS Credentials NOT found in env");
                        }

                        try {
                            $s3Client = new S3Client($config);

                            \Log::info("S3Client created, attempting listObjectsV2");

                            // List objects in the directory
                            $result = $s3Client->listObjectsV2([
                                'Bucket' => $bucketFromHost,
                                'Prefix' => $directory . '/'
                            ]);

                            \Log::info("S3 List result", [
                                'count' => isset($result['Contents']) ? count($result['Contents']) : 0,
                                'prefix_requested' => $directory . '/',
                                'result_keys' => array_keys($result->toArray())
                            ]);

                            // Generate presigned URLs for each object
                            if (isset($result['Contents']) && count($result['Contents']) > 0) {
                                $svc = new S3PresignedUrlService();
                                foreach ($result['Contents'] as $object) {
                                    $key = $object['Key'];
                                    if ($key) {
                                        $s3Uri = "s3://{$bucketFromHost}/{$key}";
                                        $presignedUrl = $svc->convertToPresignedUrl($s3Uri, 60);
                                        $images[] = $presignedUrl;

                                        \Log::debug("Added image", ['key' => $key]);
                                    }
                                }
                            } else {
                                \Log::warning("No objects found in S3 directory", [
                                    'bucket' => $bucketFromHost,
                                    'prefix' => $directory . '/',
                                    'has_contents' => isset($result['Contents'])
                                ]);
                            }
                        } catch (\Throwable $e) {
                            \Log::error("AWS SDK listObjectsV2 error", [
                                'message' => $e->getMessage(),
                                'type' => get_class($e),
                                'code' => $e->getCode(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error listing S3 images: " . $e->getMessage());
        }

        $row->images = $images;
        return response()->json($row);
    }
}
