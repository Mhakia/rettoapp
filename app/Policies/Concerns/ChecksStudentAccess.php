<?php

namespace App\Policies\Concerns;

use App\Models\Student;
use App\Models\User;

/**
 * Shared "who can see this student's data" check for the follow-up / wellbeing / alert / PIAR policies.
 */
trait ChecksStudentAccess
{
    protected function canAccessStudent(User $user, Student $student): bool
    {
        if ($student->user_id === $user->id) {
            return true;
        }

        if ($user->hasRole('institution_admin')) {
            return $user->institution_id === $student->activeMembership?->institution_id;
        }

        if ($user->hasRole('teacher')) {
            $groupId = $student->activeMembership?->group_id;

            return $groupId && $user->teacherGroups()->where('groups.id', $groupId)->exists();
        }

        if ($user->hasRole('guardian')) {
            return $user->guardianStudents()->where('students.id', $student->id)->exists();
        }

        return false;
    }
}
