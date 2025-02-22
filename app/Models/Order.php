<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'total_amount',
        'name',
        'phone',
        'address',
        'note',
        'payment_method',
        'sub_total',
        'shipping_fee',
        'status'
    ];

    public function order_details(): HasMany{
        return $this->hasMany(OrderDetail::class, 'order_number', 'order_number');
    }
}