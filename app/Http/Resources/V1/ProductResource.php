<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'description' => $this->description, 'category_id' => $this->category_id, 'category' => new CategoryResource($this->whenLoaded('category')), 'unit_price' => $this->unit_price, 'unit' => $this->unit, 'image_url' => $this->image ? Storage::disk('public')->url($this->image) : null];
    }
}
