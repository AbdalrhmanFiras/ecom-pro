<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\OrderItemResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'total' => $this->total,
            'status' => $this->status,
            'carrier' => $this->carrier,
            'Order_Date' => $this->created_at->format('Y-m-d'),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
