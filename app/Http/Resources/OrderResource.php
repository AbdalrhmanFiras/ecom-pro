<?php

namespace App\Http\Resources;
use App\Enums\OrderStatus;

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
            'carrier' => $this->carrier,
            'tracking_number' => $this->tracking_number,
            'Order_Date' => $this->created_at->format('Y-m-d h:i:s'),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'completed_at' => $this->completed_at,
            'refund_reason' => $this->when($this->status === OrderStatus::REFUNDED, $this->refund_reason),
            'cancellation_reason' => $this->when($this->status === OrderStatus::CANCELLED, $this->cancellation_reason),

            'status' => $this->status


        ];
    }
}
