<?php

namespace App\Livewire\Institutions;

use App\Models\Institution;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Institución')]
class Show extends Component
{
    public Institution $institution;

    #[Url]
    public string $tab = 'student';

    public function mount(Institution $institution): void
    {
        $this->authorize('view', $institution);
        $this->institution = $institution;
    }

    #[Computed]
    public function memberships()
    {
        if ($this->tab === 'group') {
            return collect();
        }

        return $this->institution->memberships()
            ->with(['user', 'group'])
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->role($this->tab))
            ->orderBy('started_at')
            ->get();
    }

    #[Computed]
    public function groups()
    {
        if ($this->tab !== 'group') {
            return collect();
        }

        return $this->institution->groups()->withCount('memberships')->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.institutions.show');
    }
}
