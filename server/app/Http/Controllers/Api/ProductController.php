<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;

use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        

        $query = Product::with('category');
        // $products = Product::with('category')->latest()->paginate(12);

        if ($request->filled('category_id')) {
            $request->validate([
                'category_id' => ['integer', 'exists:categories,id'],
            ]);

            $query->where('category_id', $request->category_id);
        }
        if ($request->min_price) {
            $request->validate([
                'min_price' => ['numeric', 'min:0'],
            ]);
            $query->where('unit_price', '>=', $request->min_price);
        }
        if ($request->max_price) {
            $request->validate([
                'max_price' => ['numeric', 'min:0'],
            ]);
            $query->where('unit_price', '<=', $request->max_price);
        }
        $products = $query->latest()->paginate(12);

        return ProductResource::collection($products);

        // return response()->json([
        //     'products' => $products,
        // ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('category');

        return new ProductResource($product);

        // return response()->json([
        //     'product' => $product,
        // ]);
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     //
    // }
}
