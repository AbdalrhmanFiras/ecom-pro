<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsTo(Product::class);
    }
}
