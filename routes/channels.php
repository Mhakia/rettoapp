<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('institution.{institutionId}', function ($user, $institutionId) {
    return $user->hasRole('institution_admin') && (int) $user->institution_id === (int) $institutionId;
});
