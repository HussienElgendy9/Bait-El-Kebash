<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // $categories = Category::paginate(12);
        $categories = Category::withCount('products')->paginate(12);

        return response()->json([
            'categories' => $categories,
        ]);
    }
    public function store(Request $request){
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $category = Category::create([
            'name'=>$validated['name'],
        ]);
        return response()->json([
            'message' => 'Category created successfully.',
            'category' => $category,
        ], 201);
    }
    public function update(Request $request, Category $category)
    {
        $validated =$request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $category ->update([
            'name'=>$validated['name'],
        ]);
        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $category,
        ], 200);
    }
    public function show(Category $category)
    {
        $category->load('products')->paginate(5);
        
        return response()->json([
            'category' => $category,
        ]);
    }
    public function destroy(Category $category)
    {
        $category->delete();
 
        return response()->json([
            'message' => 'Category deleted successfully.',
        ],200);
    }
}
