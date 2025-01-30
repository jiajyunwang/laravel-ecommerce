<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Redis;
use App\Models\Room;

Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    if ($user->role === 'admin') {
        return ['id' => $user->id];
    } else {
        $room = Room::where('buyer_id', $user->id)->first();
        if ($room->id === (int)$roomId) {
            return ['id' => $user->id];
        }
        return false;
    }
});
