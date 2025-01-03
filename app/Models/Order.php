<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public function order_details(): HasMany{
        return $this->hasMany(OrderDetail::class, 'order_number', 'order_number');
    }
}