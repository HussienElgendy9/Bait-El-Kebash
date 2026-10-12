<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'unit_price' => $this->unit_price,
            'unit' => $this->unit,
            'is_available' => $this->is_available,
            // 'image' => $this->image,
            'image' => $this->image
                ? asset('storage/' . $this->image)
                : null,
            // 'category' => $this->whenLoaded('category', function () {
                // return [
                //     'id' => $this->category->id,
                //     'name' => $this->category->name,
                // ];
            'category' => new CategoryResource(
                $this->whenLoaded('category')
            ),
        ];
    }
}