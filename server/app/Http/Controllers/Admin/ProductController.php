<?php

namespace App\Http\Controllers\Admin;
use App\Models\Category;
use App\Models\Product;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $products = Product::all();
        $categories = Category::all();

        return view('admin.products.index',compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();

        return view('admin.products.create', compact('categories'));
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
        'name' => 'required',
        'cat_id' => 'required|exists:categories,id',
        'unit_price' => 'required|numeric',
        'description' => 'nullable',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        $imagePath = null;

        if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('products', 'public');
    }
        Product::create([
            'name'=>$request->name,
            'category_id'=>$request->cat_id,
            'unit_price'=>$request->unit_price,
            'description'=>$request->description,
            'image' => $imagePath,
        ]);
        return redirect()->route('admin.products.index');
    }

    public function edit(string $id)
    {
        //use this code if you are not using resource
        $product = Product::findOrFail($id);
        $categories = Category::all();
        // take advantage of resource's route model binding
        return view('admin.products.edit',compact('product','categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $product = Product::findOrFail($id);
        $request->validate([
        'name' => 'required',
        'cat_id' => 'required|exists:categories,id',
        'unit_price' => 'required|numeric',
        'description' => 'nullable',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = $product->image;

        if ($request->hasFile('image')) {
            $newImage = $request->file('image')->store('products', 'public');
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $newImage;
        }
        $product->update([
            'name'=>$request->name,
            'category_id'=>$request->cat_id,
            'unit_price'=>$request->unit_price,
            'description'=>$request->description,
            'image' => $imagePath,
        ]);
        return redirect()->route('admin.products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $product = Product::findOrFail($id);
        if($product->image){
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return redirect()->route('admin.products.index')
                     ->with('success', 'Product deleted successfully.');
    }
}
