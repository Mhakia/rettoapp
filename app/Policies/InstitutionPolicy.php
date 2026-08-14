<?php

namespace App\Policies;

use App\Models\Institution;
use App\Models\User;

class InstitutionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-institutions');
    }

    public function view(User $user, Institution $institution): bool
    {
        return $user->can('view-institutions')
            || $user->institution_id === $institution->id;
    }

    public function create(User $user): bool
    {
        return $user->can('create-institution');
    }

    public function update(User $user, Institution $institution): bool
    {
        return $user->can('update-institution');
    }

    public function delete(User $user, Institution $institution): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Create/reassign the institution_admin account for an institution.
     */
    public function assignAdmin(User $user, Institution $institution): bool
    {
        return $user->can('assign-institution-admin');
    }
}
