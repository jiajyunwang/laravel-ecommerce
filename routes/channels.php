<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('user.{id}', function ($user, $id) {
    //return (int) $user->id === (int) $id;
    return true;
});
