<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PaginationRequest;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(PaginationRequest $request)
    {
        return CategoryResource::collection(Category::orderByDesc('id')->paginate($request->integer('per_page', 12))->withQueryString());
    }

    public function show(Category $category)
    {
        return new CategoryResource($category);
    }
}
