<?php

namespace App\Livewire\Actors;

use App\Models\Group;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Acudientes')]
class StaffGuardiansRoster extends Component
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
     * Keyset (cursor) pagination stays fast no matter how many guardians exist across institutions.
     * Only students matching the current filters are eager-loaded onto each guardian, so the detail
     * popup and student count never leak children from institutions/groups outside the current filter.
     */
    #[Computed]
    public function guardians()
    {
        $search = trim($this->search);
        $institutionId = $this->selectedInstitution?->id;
        $groupId = $this->groupId;

        $studentsConstraint = function ($query) use ($institutionId, $groupId) {
            $query->whereHas('activeMembership', function ($q) use ($institutionId, $groupId) {
                $institutionId && $q->where('institution_id', $institutionId);
                $groupId && $q->where('group_id', $groupId);
            })->with(['user', 'activeMembership.institution', 'activeMembership.group']);
        };

        return User::role('guardian')
            ->whereHas('guardianStudents', function ($query) use ($institutionId, $groupId) {
                $query->whereHas('activeMembership', function ($q) use ($institutionId, $groupId) {
                    $institutionId && $q->where('institution_id', $institutionId);
                    $groupId && $q->where('group_id', $groupId);
                });
            })
            ->with(['guardianStudents' => $studentsConstraint])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'ilike', "%{$search}%")
                        ->orWhere('document_number', 'ilike', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->cursorPaginate(10);
    }

    /**
     * A key that changes whenever the current page's set of guardians changes, used to force
     * Alpine to reinitialize with fresh `guardianDetails` instead of keeping stale client state.
     */
    #[Computed]
    public function guardiansCacheKey(): string
    {
        return md5($this->guardians->pluck('id')->implode(','));
    }

    /**
     * Full detail for every row on the current page, embedded once so the popup opens
     * instantly client-side with zero extra requests.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function guardianDetails(): array
    {
        return $this->guardians->getCollection()->mapWithKeys(function (User $guardian) {
            return [$guardian->id => [
                'name' => $guardian->name,
                'initials' => $guardian->initials(),
                'email' => $guardian->email,
                'phone' => $guardian->phone,
                'address' => $guardian->address,
                'document_type' => $guardian->document_type,
                'document_number' => $guardian->document_number,
                'birth_date' => $guardian->birth_date?->format('d/m/Y'),
                'students' => $guardian->guardianStudents->map(fn ($student) => [
                    'name' => $student->user->name,
                    'institution' => $student->activeMembership?->institution?->name,
                    'group' => $student->activeMembership?->group?->name,
                ])->all(),
            ]];
        })->all();
    }

    public function render()
    {
        return view('livewire.actors.staff-guardians-roster');
    }
}
