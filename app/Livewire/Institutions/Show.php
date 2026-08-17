<?php

namespace App\Livewire\Institutions;

use App\Models\Institution;
use App\Models\InstitutionMembership;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Institución')]
class Show extends Component
{
    use WithPagination;

    public Institution $institution;

    #[Url]
    public string $tab = 'student';

    public string $search = '';

    public function mount(Institution $institution): void
    {
        $this->authorize('view', $institution);
        $this->institution = $institution;
    }

    public function updatedTab(): void
    {
        $this->resetPage();
        $this->reset('search');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Keyset (cursor) pagination stays fast no matter how many members the institution has.
     */
    #[Computed]
    public function memberships()
    {
        if ($this->tab === 'group') {
            return collect();
        }

        $search = trim($this->search);

        return $this->institution->memberships()
            ->with(['group'])
            ->with($this->tab === 'student' ? ['user.studentProfile.guardians'] : ['user.teacherGroups'])
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->role($this->tab))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    // ILIKE (case-insensitive) is accelerated by the pg_trgm GIN indexes on these columns.
                    $q->whereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$search}%"));

                    if ($this->tab === 'student') {
                        $q->orWhereHas('user.studentProfile', fn ($s) => $s->where('document_number', 'ilike', "%{$search}%"));
                    } else {
                        $q->orWhereHas('user', fn ($u) => $u->where('document_number', 'ilike', "%{$search}%"));
                    }
                });
            })
            ->orderByDesc('id')
            ->cursorPaginate(10);
    }

    /**
     * A key that changes whenever the current page's set of members changes, used to force
     * Alpine to reinitialize with fresh `membershipDetails` instead of keeping stale client state.
     */
    #[Computed]
    public function membershipsCacheKey(): string
    {
        if ($this->tab === 'group') {
            return 'group';
        }

        return md5($this->tab.'|'.$this->memberships->pluck('id')->implode(','));
    }

    /**
     * Full detail for every row on the current page, embedded once so the popup opens
     * instantly client-side with zero extra requests.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function membershipDetails(): array
    {
        if ($this->tab === 'group') {
            return [];
        }

        return $this->memberships->getCollection()->mapWithKeys(function (InstitutionMembership $membership) {
            $user = $membership->user;

            $detail = [
                'name' => $user->name,
                'email' => $user->email,
                'group' => $membership->group?->name,
                'started_at' => $membership->started_at?->format('d/m/Y'),
            ];

            if ($this->tab === 'student') {
                $student = $user->studentProfile;
                $detail['document_type'] = $student?->document_type;
                $detail['document_number'] = $student?->document_number;
                $detail['birth_date'] = $student?->birth_date?->format('d/m/Y');
                $detail['guardians'] = $student?->guardians->map(fn ($g) => ['name' => $g->name, 'email' => $g->email])->all() ?? [];
            } else {
                $detail['document_type'] = $user->document_type;
                $detail['document_number'] = $user->document_number;
                $detail['groups'] = $user->teacherGroups->pluck('name')->all();
            }

            return [$membership->id => $detail];
        })->all();
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
