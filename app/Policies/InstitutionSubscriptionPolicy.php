<?php

namespace App\Policies;

use App\Models\InstitutionSubscription;
use App\Models\User;

class InstitutionSubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, InstitutionSubscription $subscription): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, InstitutionSubscription $subscription): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, InstitutionSubscription $subscription): bool
    {
        return $user->hasRole('super_admin');
    }
}
