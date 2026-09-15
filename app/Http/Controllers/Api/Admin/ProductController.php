<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->paginate(12);

        return response()->json([
            'products' => $products,
            
        ]);
    }
    public function store(Request $request){
       $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        // 'cat_id' => ['required', 'integer', 'exists:categories,id'],
        'category_id' => ['required', 'integer', 'exists:categories,id'],
        'unit_price' => ['required', 'numeric', 'min:100'],
        'unit' => ['required', 'string', 'max:50'],
        'description' => ['nullable', 'string'],
        'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $imagePath = null;

        if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('products', 'public');
        }

        // $product = Product::create([
        //     'name'=>$request->name,
        //     'category_id'=>$request->cat_id,
        //     'unit_price'=>$request->unit_price,
        //     'description'=>$request->description,
        //     'image' => $imagePath,
        // ]);
        $product = Product::create([
            'name'=>$validated['name'],
            'category_id'=>$validated['category_id'],
            'unit_price'=>$validated['unit_price'],
            'unit'=>$validated['unit'],
            'description'=>$validated['description'],
            'image' => $imagePath,
        ]);
            $product->load('category');

        return response()->json([
        'message' => 'Product created successfully.',
        'product' => $product,
    ], 201);
    }
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'unit_price' => ['sometimes', 'numeric', 'min:0'],
            'unit' => ['sometimes', 'string', 'max:50'],
            'description' => ['sometimes','nullable', 'string'],
            'image' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if (empty($validated)) {
            return response()->json([
                'message' => 'At least one field must be provided for update.',
            ], 422);
        }

        // $imagePath = $product->image;
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // $validated['image'] = $imagePath;
        $product->update($validated);
        $product->load('category');

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => $product,
        ]);
    }
    public function show(Product $product)
    {
        $product->load('category');

        return response()->json([
            'product' => $product,
        ]);
    }
    public function destroy(Product $product){
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return response()->json([
        'message' => 'Product deleted successfully.',
    ]);
    }
}
