<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderStatus;

class Order extends Model
{

    protected $fillable = ['total', 'status', 'user_id', 'quantity', 'product_id', 'order_id', 'price', 'tracking_number', 'carrier', 'delivery_confirmation', 'cancellation_reason', 'is_admin', 'completed_at'];

    protected $casts = [
        'status' => OrderStatus::class, // Cast the status field to the OrderStatus enum
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }


}
