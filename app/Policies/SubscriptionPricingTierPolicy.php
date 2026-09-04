<?php

namespace App\Policies;

use App\Models\SubscriptionPricingTier;
use App\Models\User;

class SubscriptionPricingTierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, SubscriptionPricingTier $tier): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, SubscriptionPricingTier $tier): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, SubscriptionPricingTier $tier): bool
    {
        return $user->hasRole('super_admin');
    }
}
