<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Universal Products API with filtering, price range, categories, sorting & pagination
     * 
     * Query Parameters:
     * - page: int (default: 1)
     * - per_page: int (default: 10)
     * - category_id: int or comma-separated IDs
     * - min_price: float
     * - max_price: float
     * - search: string (searches in name, description)
     * - sort: string (name, price, popularity, newest | default: newest)
     * - order: asc|desc (default: desc for newest, asc for others)
     * 
     * Example:
     * GET /api/products?page=1&per_page=12&category_id=1,2&min_price=100&max_price=5000&search=pressure&sort=price&order=asc
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variants' => function ($q) {
            $q->with(['images', 'attributes.attribute']);
        }]);

        // Search filter (by name or description)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Category filter (single or multiple)
        if ($request->has('category_id') && $request->category_id) {
            $categoryIds = is_array($request->category_id) 
                ? $request->category_id 
                : array_map('trim', explode(',', $request->category_id));
            $query->whereIn('category_id', $categoryIds);
        }

        // Price range filter
        if ($request->has('min_price') && $request->min_price !== null) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('price', '>=', (float)$request->min_price);
            }, '>=', 1);
        }

        if ($request->has('max_price') && $request->max_price !== null) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('price', '<=', (float)$request->max_price);
            }, '>=', 1);
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        $order = $request->get('order', $sort === 'newest' ? 'desc' : 'asc');

        switch ($sort) {
            case 'price':
                $query->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
                      ->selectRaw('products.*, MIN(product_variants.price) as min_variant_price')
                      ->groupBy('products.id')
                      ->orderByRaw('min_variant_price ' . strtoupper($order));
                break;
            case 'name':
                $query->orderBy('name', $order);
                break;
            case 'popularity':
                $query->orderBy('id', 'desc'); // Can be enhanced with view counts
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = min((int)$request->get('per_page', 10), 100); // Max 100 per page
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    // public function show($slug)
    // {
    //     return Product::with('category','variants.images', 'variants.attributes.attribute','related')
    //         ->where('slug', $slug)
    //         ->firstOrFail();
    // }

    public function show($slug)
    {
        $product = Product::with([
            'category',
            'variants.images',
            'variants.attributes.attribute',
        ])->where('slug', $slug)->firstOrFail();

        $product->related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['variants.images','variants.attributes.attribute'])
            ->take(4)
            ->get();

        return response()->json($product);
    }


    public function store(Request $request)
    {
        $product = Product::create($request->only([
            'name',
            'slug',
            'category_id',
            'description',
            'short_description',
            'meta_title',
            'meta_description'
        ]));

        return response()->json($product, 201);
    }
}
