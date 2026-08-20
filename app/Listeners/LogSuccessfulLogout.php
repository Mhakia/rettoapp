<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class LogSuccessfulLogout
{
    public function __construct(private Request $request) {}

    public function handle(Logout $event): void
    {
        activity('auth')
            ->causedBy($event->user instanceof Model ? $event->user : null)
            ->withProperties([
                'ip' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'guard' => $event->guard,
            ])
            ->event('logout')
            ->log('Logout');
    }
}
