<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Policies\Concerns\ChecksStudentAccess;

class StudentPolicy
{
    use ChecksStudentAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['institution_admin', 'teacher', 'guardian']);
    }

    public function view(User $user, Student $student): bool
    {
        return $this->canAccessStudent($user, $student);
    }

    /**
     * Enroll (create) a new student profile: only institution_admin.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('institution_admin');
    }
}
