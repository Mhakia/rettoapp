<?php

namespace App\Policies;

use App\Models\Alert;
use App\Models\User;
use App\Policies\Concerns\ChecksStudentAccess;

class AlertPolicy
{
    use ChecksStudentAccess;

    public function view(User $user, Alert $alert): bool
    {
        return $this->canAccessStudent($user, $alert->student);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['institution_admin', 'teacher']);
    }

    public function resolve(User $user, Alert $alert): bool
    {
        return $user->hasAnyRole(['institution_admin', 'teacher']) && $this->canAccessStudent($user, $alert->student);
    }
}
