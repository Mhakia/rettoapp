<?php

namespace App\Policies;

use App\Models\IndividualSupportPlan;
use App\Models\User;
use App\Policies\Concerns\ChecksStudentAccess;

class IndividualSupportPlanPolicy
{
    use ChecksStudentAccess;

    public function view(User $user, IndividualSupportPlan $plan): bool
    {
        return $this->canAccessStudent($user, $plan->student);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('institution_admin');
    }

    public function update(User $user, IndividualSupportPlan $plan): bool
    {
        return $user->hasRole('institution_admin') && $this->canAccessStudent($user, $plan->student);
    }
}
