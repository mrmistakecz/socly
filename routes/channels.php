<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('online', function ($user) {
    // Update last seen when joining presence channel
    $user->update(['last_seen_at' => now()]);
    
    return [
        'id' => $user->id,
        'name' => $user->name,
        'last_seen_at' => $user->last_seen_at,
    ];
});
