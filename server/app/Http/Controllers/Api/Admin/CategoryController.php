<?php

namespace App\Http\Controllers\Api\Admin;
use App\Http\Resources\CategoryResource;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        // $categories = Category::paginate(12);
        // per_page lets the dashboard load every category for the product form's select.
        $categories = Category::withCount('products')->paginate(min($request->integer('per_page', 12), 100));

        return CategoryResource::collection($categories);
        // return response()->json([
        //     'categories' => $categories,
        // ]);
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
            'category' => new CategoryResource($category),
            // 'category' => $category,
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
            'category' => new CategoryResource($category),
        ], 200);
    }
    public function show(Category $category)
    {
        $category->load('products')->paginate(5);
        
        return new CategoryResource($category);
        // return response()->json([
        //     'category' => $category,
        // ]);
    }
    public function destroy(Category $category)
    {
        // products.category_id is a restricting FK; fail cleanly instead of a 500.
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Category has products. Move or delete them first.',
            ], 422);
        }
        $category->delete();
 
        return response()->json([
            'message' => 'Category deleted successfully.',
        ],200);
    }
}
