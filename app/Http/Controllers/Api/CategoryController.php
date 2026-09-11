<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(['categories' => Category::latest()->paginate(12)]);
    }

    public function show(Category $category)
    {
        return response()->json(['category' => $category->load('products')]);
    }
}
