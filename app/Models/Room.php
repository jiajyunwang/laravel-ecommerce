<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'buyer_id',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class, 'room_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }
}