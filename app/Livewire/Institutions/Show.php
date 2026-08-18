<?php

namespace App\Livewire\Institutions;

use App\Models\Institution;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Institución')]
class Show extends Component
{
    public Institution $institution;

    public function mount(Institution $institution): void
    {
        $this->authorize('view', $institution);
        $this->institution = $institution;
    }

    public function render()
    {
        return view('livewire.institutions.show');
    }
}
