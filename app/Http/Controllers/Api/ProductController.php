<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(['products' => Product::with('category')->latest()->paginate(12)]);
    }

    public function show(Product $product)
    {
        return response()->json(['product' => $product->load('category')]);
    }
}
