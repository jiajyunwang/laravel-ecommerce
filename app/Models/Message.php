<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'room_id',
        'sender_id',
        'content',
    ];

    public function room()
    {
        return $this->belongsTo(Chat::class, 'room_id');
    }

    protected $touches = ['room'];
}