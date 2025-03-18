<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoery extends Model
{

    protected $table = 'categories';
    protected $fillable = ['type'];
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
