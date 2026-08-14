<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('institution_admin') || $user->hasRole('guardian');
    }

    public function view(User $user, Report $report): bool
    {
        if ($user->hasRole('institution_admin')) {
            return $user->institution_id === $report->institution_id;
        }

        return $report->recipient_id === $user->id;
    }
}
