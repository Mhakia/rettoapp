<?php

namespace App\Livewire\Guardians;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class ImpersonationBanner extends Component
{
    public function returnToGuardian(): void
    {
        $guardianId = Session::pull('guardian_return_id');

        abort_unless($guardianId, 404);

        Auth::loginUsingId($guardianId);
        Session::regenerate();

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.guardians.impersonation-banner');
    }
}
