<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('institution_admin');
    }

    public function view(User $user, Branch $branch): bool
    {
        return $user->institution_id === $branch->institution_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('institution_admin');
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->institution_id === $branch->institution_id;
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->institution_id === $branch->institution_id;
    }
}
