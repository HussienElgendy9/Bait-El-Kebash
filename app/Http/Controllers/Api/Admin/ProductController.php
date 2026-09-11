<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;


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
        
    }
}
