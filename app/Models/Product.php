<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // use HasFactory;
    protected $fillable = ['name', 'price', 'description', 'stock', 'user_id', 'Categoery', 'warranty', 'review', 'rating'];

    public function withlists()
    {
        return $this->hasMany(Product::class);
    }

    public function categoerys()
    {
        return $this->belongsTo(Categoery::class);
    }

    public function reviews()
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
