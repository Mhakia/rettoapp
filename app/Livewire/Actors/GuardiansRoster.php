<?php

namespace App\Livewire\Actors;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class GuardiansRoster extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Guardians relevant to this institution: at least one linked student with an active
     * membership here. A guardian's other children (if any) in other institutions stay hidden.
     */
    #[Computed]
    public function guardians()
    {
        $institutionId = Auth::user()->institution_id;
        $search = trim($this->search);

        return User::role('guardian')
            ->whereHas('guardianStudents.activeMembership', fn ($q) => $q->where('institution_id', $institutionId))
            ->with(['guardianStudents' => function ($query) use ($institutionId) {
                $query->whereHas('activeMembership', fn ($q) => $q->where('institution_id', $institutionId))
                    ->with(['user', 'activeMembership.group']);
            }])
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
                    'group' => $student->activeMembership?->group?->name,
                ])->all(),
            ]];
        })->all();
    }

    #[On('guardian-students-updated')]
    public function refreshGuardians(): void
    {
        unset($this->guardians, $this->guardiansCacheKey, $this->guardianDetails);
    }

    public function render()
    {
        return view('livewire.actors.guardians-roster');
    }
}
