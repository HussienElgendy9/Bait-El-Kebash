<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PaginationRequest;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(PaginationRequest $request)
    {
        $query = Product::with('category')
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->input('q').'%'));
        match ($request->input('sort', 'newest')) {
            'price_asc' => $query->orderBy('unit_price'),
            'price_desc' => $query->orderByDesc('unit_price'),
            default => $query->orderByDesc('id'),
        };

        return ProductResource::collection($query->orderBy('id')->paginate($request->integer('per_page', 12))->withQueryString());
    }

    public function show(Product $product)
    {
        return new ProductResource($product->load('category'));
    }
}
