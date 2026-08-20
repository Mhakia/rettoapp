<?php

namespace App\Livewire\Alerts;

use App\Models\Alert;
use App\Models\Student;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Nueva alerta')]
class CreateAlert extends Component
{
    public string $studentSearch = '';

    public string $student_id = '';

    public string $type = '';

    public string $severity = 'low';

    public string $message = '';

    public function mount(): void
    {
        $this->authorize('create', Alert::class);
    }

    /**
     * Scoped to the actor's own students: their institution (institution_admin) or their own
     * groups (teacher) — never the whole platform.
     */
    #[Computed]
    public function students()
    {
        $search = trim($this->studentSearch);

        $query = Student::query()->whereHas('activeMembership', fn ($q) => $this->scopeToActor($q));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$search}%"))
                    ->orWhere('document_number', 'ilike', "%{$search}%");
            });
        }

        return $query->with('user:id,name')
            ->orderBy(User::select('name')->whereColumn('users.id', 'students.user_id'))
            ->limit(20)
            ->get();
    }

    private function scopeToActor($query): void
    {
        $user = Auth::user();

        if ($user->hasRole('teacher')) {
            $query->whereIn('group_id', $user->teacherGroups()->pluck('groups.id'));
        } else {
            $query->where('institution_id', $user->institution_id);
        }
    }

    public function store(): void
    {
        $this->authorize('create', Alert::class);

        $data = $this->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'type' => ['required', 'string', 'max:255'],
            'severity' => ['required', 'in:low,medium,high'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $student = Student::findOrFail($data['student_id']);

        // Re-check server-side that this student is actually reachable by the actor: never trust the client-picked id.
        abort_unless($this->canTargetStudent($student), 403);

        Alert::create([
            'student_id' => $student->id,
            'institution_membership_id' => $student->activeMembership?->id,
            'type' => $data['type'],
            'severity' => $data['severity'],
            'message' => $data['message'],
            'status' => 'open',
            'created_by' => Auth::id(),
        ]);

        Flux::toast(variant: 'success', text: __('Alerta creada.'));
        $this->redirect(route('alerts.index'), navigate: true);
    }

    private function canTargetStudent(Student $student): bool
    {
        $user = Auth::user();
        $membership = $student->activeMembership;

        if (! $membership) {
            return false;
        }

        if ($user->hasRole('teacher')) {
            return $user->teacherGroups()->where('groups.id', $membership->group_id)->exists();
        }

        return $user->institution_id === $membership->institution_id;
    }

    public function render()
    {
        return view('livewire.alerts.create-alert');
    }
}
