<?php

namespace App\Http\Controllers\Store;
use App\Models\Category;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(){
        $products = Product::with('category')->get();
        // $products = Product::with('category')
        // ->latest()
        // ->paginate(12);
        return view('store.shop',compact('products'));
    }
    public function show($id){
        $product = Product::with('category')->findOrFail($id);

return view('store.product', compact('product'));
        return view('store.product',compact('product'));
    }
}
