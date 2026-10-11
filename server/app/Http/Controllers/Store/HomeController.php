<?php

namespace App\Http\Controllers\Store;
use App\Models\Product;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
       
        $products = Product::latest()->take(8)->get();
        $categories = Category::all();

        return view('store.index',compact('products', 'categories'));
    }

    // public function shop()
    // {
       
        
    // }
}
