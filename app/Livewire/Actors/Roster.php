<?php

namespace App\Livewire\Actors;

use App\Models\InstitutionMembership;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Estudiantes y profesores')]
class Roster extends Component
{
    public string $role = 'student';

    #[Computed]
    public function memberships()
    {
        return InstitutionMembership::with(['user', 'group'])
            ->where('institution_id', Auth::user()->institution_id)
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->role($this->role))
            ->orderBy('started_at')
            ->get();
    }

    #[On('membership-withdrawn')]
    public function refreshMemberships(): void
    {
        unset($this->memberships);
    }

    public function render()
    {
        return view('livewire.actors.roster');
    }
}
