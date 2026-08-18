<?php

namespace App\Livewire\Actors;

use App\Models\Student;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ManageGuardianStudents extends Component
{
    #[Locked]
    public int $guardianId;

    public string $studentSearch = '';

    /**
     * @var array<int, int>
     */
    public array $student_ids = [];

    public function mount(User $guardian): void
    {
        $this->guardianId = $guardian->id;

        $institutionId = Auth::user()->institution_id;

        $this->student_ids = $guardian->guardianStudents()
            ->whereHas('activeMembership', fn ($q) => $q->where('institution_id', $institutionId))
            ->pluck('students.id')
            ->all();
    }

    /**
     * Active students of this institution, searchable, so the admin can add new ones to link.
     */
    #[Computed]
    public function students()
    {
        $institutionId = Auth::user()->institution_id;
        $search = trim($this->studentSearch);

        return Student::with('user')
            ->whereHas('activeMembership', fn ($q) => $q->where('institution_id', $institutionId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$search}%"))
                        ->orWhere('document_number', 'ilike', "%{$search}%");
                });
            })
            ->orderBy(User::select('name')->whereColumn('users.id', 'students.user_id'))
            ->limit(50)
            ->get();
    }

    public function save(): void
    {
        $institution = Auth::user()->institution;

        $this->authorize('manageActors', $institution);

        $data = $this->validate([
            'student_ids' => ['array'],
            'student_ids.*' => ['integer'],
        ]);

        $guardian = User::findOrFail($this->guardianId);

        // Only touch links to students of THIS institution: a guardian's children elsewhere must stay untouched.
        $institutionStudentIds = Student::whereHas('activeMembership', fn ($q) => $q->where('institution_id', $institution->id))
            ->pluck('id')
            ->all();

        $requestedIds = array_values(array_intersect($data['student_ids'], $institutionStudentIds));

        $currentIds = $guardian->guardianStudents()
            ->whereIn('students.id', $institutionStudentIds)
            ->pluck('students.id')
            ->all();

        $toAttach = array_values(array_diff($requestedIds, $currentIds));
        $toDetach = array_values(array_diff($currentIds, $requestedIds));

        if ($toDetach !== []) {
            $guardian->guardianStudents()->detach($toDetach);
        }

        if ($toAttach !== []) {
            $guardian->guardianStudents()->attach($toAttach);
        }

        Flux::toast(variant: 'success', text: __('Estudiantes del acudiente actualizados.'));
        $this->dispatch('modal-close', name: "manage-guardian-{$this->guardianId}");
        $this->dispatch('guardian-students-updated');
    }

    public function render()
    {
        return view('livewire.actors.manage-guardian-students');
    }
}
