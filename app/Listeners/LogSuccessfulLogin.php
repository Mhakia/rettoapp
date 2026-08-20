<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    public function __construct(private Request $request) {}

    public function handle(Login $event): void
    {
        activity('auth')
            ->causedBy($event->user instanceof Model ? $event->user : null)
            ->withProperties([
                'ip' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'guard' => $event->guard,
            ])
            ->event('login')
            ->log('Successful login');
    }
}
