<?php

namespace App\Livewire\Actors;

use App\Models\InstitutionMembership;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Estudiantes')]
class StudentsRoster extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Keyset (cursor) pagination stays fast no matter how many students the institution has.
     */
    #[Computed]
    public function memberships()
    {
        $search = trim($this->search);

        return InstitutionMembership::with(['group', 'user.studentProfile.guardians'])
            ->where('institution_id', Auth::user()->institution_id)
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->role('student'))
            ->when($search !== '', function ($query) use ($search) {
                // ILIKE (case-insensitive) is accelerated by the pg_trgm GIN indexes on these columns.
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$search}%"))
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
                'group' => $membership->group?->name,
                'started_at' => $membership->started_at?->format('d/m/Y'),
                'document_type' => $student?->document_type,
                'document_number' => $student?->document_number,
                'birth_date' => $student?->birth_date?->format('d/m/Y'),
                'guardians' => $student?->guardians->map(fn ($g) => ['name' => $g->name, 'email' => $g->email])->all() ?? [],
            ]];
        })->all();
    }

    #[On('membership-withdrawn')]
    #[On('membership-groups-updated')]
    #[On('member-reenrolled')]
    public function refreshMemberships(): void
    {
        unset($this->memberships, $this->membershipsCacheKey, $this->membershipDetails);
    }

    public function render()
    {
        return view('livewire.actors.students-roster');
    }
}
