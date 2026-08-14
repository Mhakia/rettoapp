<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['institution_admin', 'teacher']);
    }

    public function view(User $user, Group $group): bool
    {
        return $user->institution_id === $group->institution_id
            || $user->teacherGroups()->where('groups.id', $group->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole('institution_admin');
    }

    public function update(User $user, Group $group): bool
    {
        return $user->institution_id === $group->institution_id;
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->institution_id === $group->institution_id;
    }
}
