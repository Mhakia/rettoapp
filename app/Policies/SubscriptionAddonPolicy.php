<?php

namespace App\Policies;

use App\Models\SubscriptionAddon;
use App\Models\User;

class SubscriptionAddonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, SubscriptionAddon $addon): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, SubscriptionAddon $addon): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, SubscriptionAddon $addon): bool
    {
        return $user->hasRole('super_admin');
    }
}
