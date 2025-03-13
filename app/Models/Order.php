<?php

namespace App\Models;
use App\Enums\OrderStatus;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $fillable = ['total', 'status', 'user_id', 'quantity', 'product_id', 'order_id', 'price'];

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
