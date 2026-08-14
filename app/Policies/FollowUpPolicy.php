<?php

namespace App\Policies;

use App\Models\FollowUp;
use App\Models\User;
use App\Policies\Concerns\ChecksStudentAccess;

class FollowUpPolicy
{
    use ChecksStudentAccess;

    public function view(User $user, FollowUp $followUp): bool
    {
        return $this->canAccessStudent($user, $followUp->student);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['institution_admin', 'teacher']);
    }
}
