<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Redis;
use App\Models\Room;

Broadcast::channel('user.{userId}', function ($user, $userId) {
    if ($user->role === 'admin') {
        return ['id' => $user->id];
    } else {
        if ($user->id === (int)$userId) {
            return ['id' => $user->id];
        }
        return false;
    }
});
