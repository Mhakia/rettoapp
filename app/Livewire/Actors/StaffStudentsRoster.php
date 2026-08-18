<?php

namespace App\Livewire\Actors;

use App\Models\Group;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Estudiantes')]
class StaffStudentsRoster extends Component
{
    use WithPagination;

    /**
     * Optional institution filter: pre-filled when arriving from an institution's own page.
     */
    #[Url(as: 'institution')]
    public ?string $institutionUuid = null;

    public ?int $groupId = null;

    public string $search = '';

    public function mount(): void
    {
        abort_unless(Auth::user()->can('view-institution-members'), 403);
    }

    public function updatedInstitutionUuid(): void
    {
        $this->groupId = null;
        $this->resetPage();
    }

    public function updatedGroupId(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function institutions()
    {
        return Institution::orderBy('name')->get(['id', 'uuid', 'name']);
    }

    #[Computed]
    public function selectedInstitution(): ?Institution
    {
        return $this->institutionUuid
            ? $this->institutions->firstWhere('uuid', $this->institutionUuid)
            : null;
    }

    #[Computed]
    public function groups()
    {
        if (! $this->selectedInstitution) {
            return collect();
        }

        return Group::where('institution_id', $this->selectedInstitution->id)->orderBy('name')->get();
    }

    /**
     * Keyset (cursor) pagination stays fast no matter how many students exist across institutions.
     */
    #[Computed]
    public function memberships()
    {
        $search = trim($this->search);

        return InstitutionMembership::with(['group', 'institution', 'user.studentProfile.guardians'])
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->role('student'))
            ->when($this->selectedInstitution, fn ($query) => $query->where('institution_id', $this->selectedInstitution->id))
            ->when($this->groupId, fn ($query) => $query->where('group_id', $this->groupId))
            ->when($search !== '', function ($query) use ($search) {
                // ILIKE (case-insensitive) is accelerated by the pg_trgm GIN indexes on these columns.
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$search}%"))
                        ->orWhereHas('user.studentProfile', fn ($s) => $s->where('document_number', 'ilike', "%{$search}%"));
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
        return md5($this->memberships->pluck('id')->implode(','));
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
        return $this->memberships->getCollection()->mapWithKeys(function (InstitutionMembership $membership) {
            $user = $membership->user;
            $student = $user->studentProfile;

            return [$membership->id => [
                'name' => $user->name,
                'initials' => $user->initials(),
                'email' => $user->email,
                'institution' => $membership->institution->name,
                'group' => $membership->group?->name,
                'started_at' => $membership->started_at?->format('d/m/Y'),
                'document_type' => $student?->document_type,
                'document_number' => $student?->document_number,
                'birth_date' => $student?->birth_date?->format('d/m/Y'),
                'guardians' => $student?->guardians->map(fn ($g) => ['name' => $g->name, 'email' => $g->email])->all() ?? [],
            ]];
        })->all();
    }

    #[On('membership-groups-updated')]
    public function refreshMemberships(): void
    {
        unset($this->memberships, $this->membershipsCacheKey, $this->membershipDetails);
    }

    public function render()
    {
        return view('livewire.actors.staff-students-roster');
    }
}
