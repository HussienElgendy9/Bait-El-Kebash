<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Requests\V1\CategoryRequest;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;

class CategoryController extends \App\Http\Controllers\Api\V1\CategoryController
{
    public function store(CategoryRequest $request)
    {
        return (new CategoryResource(Category::create($request->validated())))->response()->setStatusCode(201);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->noContent();
    }
}
