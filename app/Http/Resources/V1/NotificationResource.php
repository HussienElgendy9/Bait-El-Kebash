<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'order_id' => $this->data['order_id'] ?? null, 'message' => $this->data['message'] ?? null, 'read_at' => $this->read_at?->toISOString(), 'created_at' => $this->created_at?->toISOString()];
    }
}
