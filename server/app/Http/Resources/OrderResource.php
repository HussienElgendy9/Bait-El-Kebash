<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'total' => $this->whenLoaded('orderItems', fn () => $this->orderItems->sum('price_snapshot')),
            'created_at' => $this->created_at,

            'user' => new UserResource($this->whenLoaded('user')),

            'order_items' => OrderItemResource::collection(
                $this->whenLoaded('orderItems')
            ),
        ];
    }
}