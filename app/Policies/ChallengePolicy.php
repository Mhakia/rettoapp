<?php

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;

class ChallengePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Challenge $challenge): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('create-challenge');
    }

    public function update(User $user, Challenge $challenge): bool
    {
        return $user->can('create-challenge') && $challenge->created_by === $user->id;
    }

    public function delete(User $user, Challenge $challenge): bool
    {
        return $user->can('create-challenge') && $challenge->created_by === $user->id;
    }

    public function viewStatistics(User $user): bool
    {
        return $user->can('view-challenge-statistics');
    }

    /**
     * Complete a challenge: must hold the permission and match the challenge's target_role.
     */
    public function complete(User $user, Challenge $challenge): bool
    {
        return $user->can('complete-challenge') && $user->hasRole($challenge->target_role);
    }
}
