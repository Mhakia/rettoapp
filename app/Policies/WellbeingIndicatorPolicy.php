<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WellbeingIndicator;
use App\Policies\Concerns\ChecksStudentAccess;

class WellbeingIndicatorPolicy
{
    use ChecksStudentAccess;

    public function view(User $user, WellbeingIndicator $indicator): bool
    {
        return $this->canAccessStudent($user, $indicator->student);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['institution_admin', 'teacher']);
    }
}
