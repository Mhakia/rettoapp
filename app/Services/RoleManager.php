<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Spatie's role/permission mutations (syncPermissions/syncRoles) are pivot-table writes and
 * never fire model events, so every mutation must go through here to stay audited.
 */
class RoleManager
{
    /**
     * @param  array<int, string>  $permissionNames
     */
    public function syncPermissions(Role $role, array $permissionNames, User $actor): Role
    {
        $before = $role->permissions()->pluck('name')->sort()->values()->all();

        $role->syncPermissions($permissionNames);

        $after = $role->permissions()->pluck('name')->sort()->values()->all();

        activity('roles')
            ->causedBy($actor)
            ->performedOn($role)
            ->withProperties(['before' => $before, 'after' => $after])
            ->event('permissions-updated')
            ->log('Role permissions updated');

        return $role->fresh('permissions');
    }

    /**
     * @param  array<int, string>|string  $roleNames
     */
    public function syncRoles(User $user, array|string $roleNames, User $actor): User
    {
        $before = $user->roles()->pluck('name')->sort()->values()->all();

        $user->syncRoles($roleNames);

        $after = $user->roles()->pluck('name')->sort()->values()->all();

        activity('roles')
            ->causedBy($actor)
            ->performedOn($user)
            ->withProperties(['before' => $before, 'after' => $after])
            ->event('roles-updated')
            ->log('User roles updated');

        return $user->fresh('roles');
    }
}
