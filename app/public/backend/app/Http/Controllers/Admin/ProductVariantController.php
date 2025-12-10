<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function show($id)
    {
        return ProductVariant::with('product', 'attributes.attribute')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $variant = ProductVariant::create($request->only([
            'product_id', 'sku', 'price', 'stock', 'image'
        ]));

        if ($request->has('attribute_value_ids')) {
            $variant->attributes()->sync($request->attribute_value_ids);
        }

        return response()->json($variant->load('attributes.attribute'), 201);
    }
}
