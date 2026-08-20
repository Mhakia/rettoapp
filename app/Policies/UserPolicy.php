<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Roles managed from the internal-users admin screens (admin/users/*).
     *
     * @var array<int, string>
     */
    public const INTERNAL_ROLES = ['super_admin', 'manager', 'pedagogue'];

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('super_admin') && $model->hasAnyRole(self::INTERNAL_ROLES);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('super_admin') && $model->hasAnyRole(self::INTERNAL_ROLES);
    }

    /**
     * "Delete" here means deactivate (soft delete): a super_admin can never deactivate themselves
     * through this panel, to avoid locking everyone out of the admin area by mistake.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('super_admin') && $model->hasAnyRole(self::INTERNAL_ROLES) && $user->isNot($model);
    }
}
