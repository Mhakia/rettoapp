<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Suscripción')]
class Billing extends Component
{
    #[Computed]
    public function institution()
    {
        return Auth::user()->institution;
    }

    public function manage()
    {
        return $this->institution->redirectToBillingPortal(route('billing.edit'));
    }

    public function render()
    {
        return view('livewire.settings.billing');
    }
}
