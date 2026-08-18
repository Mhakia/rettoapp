<?php

namespace App\Livewire\Actors;

use App\Models\Group;
use App\Models\InstitutionMembership;
use App\Models\Student;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ReenrollMember extends Component
{
    #[Locked]
    public string $role;

    public string $document_type = '';

    public string $document_number = '';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $found = null;

    public ?int $group_id = null;

    /**
     * @var array<int, int>
     */
    public array $group_ids = [];

    public function mount(string $role): void
    {
        $this->role = $role;

        $this->authorize('manageActors', Auth::user()->institution);
    }

    public function updatedDocumentType(): void
    {
        $this->search();
    }

    public function updatedDocumentNumber(): void
    {
        $this->search();
    }

    protected function search(): void
    {
        $this->reset(['group_id', 'group_ids']);
        $this->found = null;

        if ($this->document_type === '' || $this->document_number === '') {
            return;
        }

        $institutionId = Auth::user()->institution_id;

        if ($this->role === 'student') {
            $student = Student::where('document_type', $this->document_type)
                ->where('document_number', $this->document_number)
                ->with('user')
                ->first();

            if (! $student) {
                $this->found = ['status' => 'not_found'];

                return;
            }

            $this->found = $this->describe($student->user, $student->activeMembership, $institutionId, $student->id);
        } else {
            $user = User::role('teacher')
                ->where('document_type', $this->document_type)
                ->where('document_number', $this->document_number)
                ->first();

            if (! $user) {
                $this->found = ['status' => 'not_found'];

                return;
            }

            $this->found = $this->describe($user, $user->activeMembership()->first(), $institutionId, null);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function describe(User $user, ?InstitutionMembership $active, int $institutionId, ?int $studentId): array
    {
        if ($active) {
            return [
                'status' => $active->institution_id === $institutionId ? 'active_here' : 'active_elsewhere',
                'name' => $user->name,
                'user_id' => $user->id,
                'institution_name' => $active->institution_id === $institutionId ? null : $active->institution->name,
            ];
        }

        return [
            'status' => 'withdrawn',
            'name' => $user->name,
            'user_id' => $user->id,
            'student_id' => $studentId,
        ];
    }

    #[Computed]
    public function groups()
    {
        return Group::where('institution_id', Auth::user()->institution_id)->orderBy('name')->get();
    }

    public function reenroll(): void
    {
        abort_unless($this->found && $this->found['status'] === 'withdrawn', 422);

        $institutionId = Auth::user()->institution_id;
        $userId = $this->found['user_id'];

        if ($this->role === 'student') {
            $data = $this->validate([
                'group_id' => ['required', 'exists:groups,id,institution_id,'.$institutionId],
            ]);

            InstitutionMembership::create([
                'user_id' => $userId,
                'institution_id' => $institutionId,
                'group_id' => $data['group_id'],
                'status' => 'active',
                'started_at' => now(),
            ]);
        } else {
            $data = $this->validate([
                'group_ids' => ['array'],
                'group_ids.*' => ['integer', 'exists:groups,id,institution_id,'.$institutionId],
            ]);

            $membership = InstitutionMembership::create([
                'user_id' => $userId,
                'institution_id' => $institutionId,
                'status' => 'active',
                'started_at' => now(),
            ]);

            if (! empty($data['group_ids'])) {
                User::find($userId)->teacherGroups()->attach(
                    collect($data['group_ids'])->mapWithKeys(fn ($id) => [$id => ['institution_membership_id' => $membership->id]])->all()
                );
            }
        }

        Flux::toast(variant: 'success', text: __('Matrícula reactivada.'));
        $this->reset(['document_type', 'document_number', 'found', 'group_id', 'group_ids']);
        $this->dispatch('modal-close', name: "reenroll-{$this->role}");
        $this->dispatch('member-reenrolled');
    }

    public function render()
    {
        return view('livewire.actors.reenroll-member');
    }
}
