<?php

namespace App\Policies;

use App\Models\ClassSession;
use App\Models\Group;
use App\Models\User;

class ClassSessionPolicy
{
    /**
     * A teacher may open a session only for a group assigned to them.
     */
    public function create(User $user, Group $group): bool
    {
        return $user->teacherGroups()->where('groups.id', $group->id)->exists();
    }

    /**
     * Any teacher assigned to the session's group may close it, not just its creator.
     */
    public function update(User $user, ClassSession $classSession): bool
    {
        return $user->teacherGroups()->where('groups.id', $classSession->group_id)->exists();
    }
}
