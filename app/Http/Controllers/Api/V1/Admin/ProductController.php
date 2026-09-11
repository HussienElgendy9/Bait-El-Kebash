<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Requests\V1\ProductRequest;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use App\Services\ProductImages;

class ProductController extends \App\Http\Controllers\Api\V1\ProductController
{
    public function store(ProductRequest $request, ProductImages $images)
    {
        return (new ProductResource($images->save(new Product, $request->validated())->refresh()->load('category')))->response()->setStatusCode(201);
    }

    public function update(ProductRequest $request, Product $product, ProductImages $images)
    {
        return new ProductResource($images->save($product, $request->validated())->load('category'));
    }

    public function destroy(Product $product, ProductImages $images)
    {
        $images->delete($product);

        return response()->noContent();
    }
}
