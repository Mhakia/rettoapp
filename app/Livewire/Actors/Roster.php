<?php

namespace App\Livewire\Actors;

use App\Models\InstitutionMembership;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Estudiantes y profesores')]
class Roster extends Component
{
    use WithPagination;

    public string $role = 'student';

    public string $search = '';

    public function updatedRole(): void
    {
        $this->resetPage();
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
        $search = trim($this->search);

        return InstitutionMembership::with(['group'])
            ->with($this->role === 'student' ? ['user.studentProfile.guardians'] : ['user.teacherGroups'])
            ->where('institution_id', Auth::user()->institution_id)
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->role($this->role))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    // ILIKE (case-insensitive) is accelerated by the pg_trgm GIN indexes on these columns.
                    $q->whereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$search}%"));

                    if ($this->role === 'student') {
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
        return md5($this->role.'|'.$this->memberships->pluck('id')->implode(','));
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

            $detail = [
                'name' => $user->name,
                'email' => $user->email,
                'group' => $membership->group?->name,
                'started_at' => $membership->started_at?->format('d/m/Y'),
            ];

            if ($this->role === 'student') {
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

    #[On('membership-withdrawn')]
    public function refreshMemberships(): void
    {
        unset($this->memberships, $this->membershipsCacheKey, $this->membershipDetails);
    }

    public function render()
    {
        return view('livewire.actors.roster');
    }
}
