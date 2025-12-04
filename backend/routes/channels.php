<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel for user-specific events (surveys, notifications)
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public channel for incidents (all authenticated users can listen)
Broadcast::channel('incidents', function ($user) {
    return $user !== null;
});
