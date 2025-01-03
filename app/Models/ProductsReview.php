<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductsReview extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'rate',
        'review',
    ];

    public function users(){
        return $this->belongsTo(User::class, 'user_id');
    }
}