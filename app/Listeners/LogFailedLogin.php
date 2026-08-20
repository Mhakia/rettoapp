<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class LogFailedLogin
{
    public function __construct(private Request $request) {}

    public function handle(Failed $event): void
    {
        // Never log the plaintext password, only the attempted identifier(s).
        activity('auth')
            ->causedBy($event->user)
            ->withProperties([
                'ip' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'guard' => $event->guard,
                'attempted' => Arr::except($event->credentials, ['password']),
            ])
            ->event('failed')
            ->log('Failed login attempt');
    }
}
