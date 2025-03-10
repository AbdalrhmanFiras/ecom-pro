<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{


    public function withlists()
    {
        return $this->hasMany(Product::class);
    }

    public function categoerys()
    {
        return $this->belongsTo(Categoery::class);
    }

    public function reivews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
