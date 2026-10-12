<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\CategoryResource;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::orderBy('id')->paginate(12);

        return CategoryResource::collection($categories);



    }


    public function show(Category $category)
    {
        $category->load('products');

        return new CategoryResource($category);


    }

}
