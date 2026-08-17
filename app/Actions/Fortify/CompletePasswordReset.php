<?php

namespace App\Actions\Fortify;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Str;

class CompletePasswordReset
{
    /**
     * Roles that manage the platform itself and never go through the
     * "first login sets a new password" activation flow.
     */
    protected const INTERNAL_ROLES = ['super_admin', 'manager', 'pedagogue'];

    /**
     * Complete the password reset process for the given user.
     *
     * For non-internal roles (institution_admin, teacher, student, guardian),
     * a reset from an unverified state is treated as their first-login
     * activation: it verifies the email and logs them straight into the
     * dashboard, same as clicking an email verification link.
     *
     * @param  mixed  $user
     */
    public function __invoke(StatefulGuard $guard, $user): void
    {
        $user->setRememberToken(Str::random(60));

        $isFirstLogin = is_null($user->email_verified_at) && ! $user->hasAnyRole(self::INTERNAL_ROLES);

        if ($isFirstLogin) {
            $user->forceFill(['email_verified_at' => now()]);
        }

        $user->save();

        event(new PasswordReset($user));

        if ($isFirstLogin) {
            $guard->login($user);
        }
    }
}
