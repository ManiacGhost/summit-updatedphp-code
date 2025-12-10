<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::with('children')->get();
    }

    public function show($slug)
    {
        return Category::with('products.variants')->where('slug', $slug)->firstOrFail();
    }
}
