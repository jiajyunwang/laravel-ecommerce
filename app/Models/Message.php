<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'room_id',
        'sender_id',
        'content',
        'is_read'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    protected $touches = ['room'];
}