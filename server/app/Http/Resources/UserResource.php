<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'role' => $this->when(
                $request->user()?->isAdmin(),
                $this->role
            ),
            'orders_count' => $this->when(
                isset($this->orders_count),
                $this->orders_count
            ),
            'orders' => OrderResource::collection(
                $this->whenLoaded('orders')
            ),
        ];
    }
}