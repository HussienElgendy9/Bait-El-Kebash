<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'user_id' => $this->user_id, 'status' => $this->status, 'delivery' => ['name' => $this->delivery_name, 'phone_number' => $this->delivery_phone, 'address' => $this->delivery_address], 'items' => OrderItemResource::collection($this->orderitems), 'total' => $this->resource->total(), 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
