<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Cart;

class Product extends Model
{

    protected $fillable = [
        'title',
        'slug',
        'description',
        'price',
        'photo',
        'size',
        'stock',
        'is_featured',
        'condition',
    ];

}
