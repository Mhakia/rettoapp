<?php

namespace App\Policies;

use App\Models\Institution;
use App\Models\User;

class InstitutionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'manager']);
    }

    public function view(User $user, Institution $institution): bool
    {
        return $user->hasAnyRole(['super_admin', 'manager'])
            || $user->institution_id === $institution->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'manager']);
    }

    public function update(User $user, Institution $institution): bool
    {
        return $user->hasAnyRole(['super_admin', 'manager']);
    }

    public function delete(User $user, Institution $institution): bool
    {
        return $user->hasRole('super_admin');
    }
}
