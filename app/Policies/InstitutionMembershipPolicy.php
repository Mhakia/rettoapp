<?php

namespace App\Policies;

use App\Models\InstitutionMembership;
use App\Models\User;

class InstitutionMembershipPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('institution_admin');
    }

    /**
     * Withdraw a membership: only the institution that currently holds it active.
     */
    public function withdraw(User $user, InstitutionMembership $membership): bool
    {
        return $user->hasRole('institution_admin')
            && $membership->status === 'active'
            && $user->institution_id === $membership->institution_id;
    }
}
