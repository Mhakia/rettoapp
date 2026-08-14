<?php

namespace App\Policies;

use App\Models\ChallengeCompletion;
use App\Models\User;

class ChallengeCompletionPolicy
{
    public function view(User $user, ChallengeCompletion $completion): bool
    {
        return $completion->user_id === $user->id
            || ($user->hasRole('teacher') && $completion->challenge->target_role === 'student')
            || $user->hasAnyRole(['super_admin', 'pedagogue']);
    }

    /**
     * Verify a student's completion: only a teacher of the student's active group.
     */
    public function verify(User $user, ChallengeCompletion $completion): bool
    {
        if (! $user->can('verify-challenge') || ! $user->hasRole('teacher')) {
            return false;
        }

        if ($completion->challenge->target_role !== 'student' || ! $completion->membership) {
            return false;
        }

        return $user->teacherGroups()
            ->where('groups.id', $completion->membership->group_id)
            ->exists();
    }
}
