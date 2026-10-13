<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Each person can only listen to their own notifications.
Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return $user->status === User::STATUS_ACTIVE && (int) $user->id === $id;
});