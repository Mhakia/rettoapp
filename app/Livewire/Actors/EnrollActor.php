<?php

namespace App\Livewire\Actors;

use App\Models\Group;
use App\Models\InstitutionMembership;
use App\Models\Student;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Matricular / vincular')]
class EnrollActor extends Component
{
    public string $role = 'student';

    public string $document_type = '';

    public string $document_number = '';

    public string $name = '';

    public string $email = '';

    public ?int $group_id = null;

    public ?string $existing_student_uuid = null;

    #[Computed]
    public function groups()
    {
        return Group::where('institution_id', Auth::user()->institution_id)->orderBy('name')->get();
    }

    #[Computed]
    public function students()
    {
        return Student::whereHas('activeMembership', function ($query) {
            $query->where('institution_id', Auth::user()->institution_id);
        })->with('user')->get();
    }

    public function updatedRole(): void
    {
        $this->reset(['document_type', 'document_number', 'name', 'email', 'group_id', 'existing_student_uuid']);
    }

    public function enroll(): void
    {
        $this->authorize('create', Student::class);

        $data = $this->validate([
            'role' => ['required', Rule::in(['student', 'teacher', 'guardian'])],
            'document_type' => ['required', 'string'],
            'document_number' => ['required', 'string'],
            'name' => ['required_unless:role,guardian_existing', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'group_id' => ['required_if:role,student', 'nullable', 'exists:groups,id'],
            'existing_student_uuid' => ['required_if:role,guardian', 'nullable', 'exists:students,uuid'],
        ]);

        match ($this->role) {
            'student' => $this->enrollStudent($data),
            'teacher' => $this->enrollTeacher($data),
            'guardian' => $this->linkGuardian($data),
        };
    }

    protected function enrollStudent(array $data): void
    {
        $institutionId = Auth::user()->institution_id;

        $student = Student::where('document_type', $data['document_type'])
            ->where('document_number', $data['document_number'])
            ->first();

        if (! $student) {
            DB::transaction(function () use ($data, $institutionId, &$student) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'] ?: "{$data['document_number']}@students.tereto.local",
                    'password' => Hash::make(Str::random(32)),
                ]);
                $user->assignRole('student');

                $student = Student::create([
                    'user_id' => $user->id,
                    'document_type' => $data['document_type'],
                    'document_number' => $data['document_number'],
                ]);

                InstitutionMembership::create([
                    'user_id' => $user->id,
                    'institution_id' => $institutionId,
                    'group_id' => $data['group_id'],
                    'status' => 'active',
                    'started_at' => now(),
                ]);
            });

            Flux::toast(variant: 'success', text: __('Estudiante matriculado.'));
            $this->reset(['document_type', 'document_number', 'name', 'email', 'group_id']);

            return;
        }

        $activeMembership = $student->activeMembership;

        if ($activeMembership) {
            if ($activeMembership->institution_id !== $institutionId) {
                Flux::toast(variant: 'danger', text: __('Ya está matriculado activamente en otra institución.'));
            } else {
                Flux::toast(variant: 'danger', text: __('Ya está matriculado activamente en tu institución.'));
            }

            return;
        }

        InstitutionMembership::create([
            'user_id' => $student->user_id,
            'institution_id' => $institutionId,
            'group_id' => $data['group_id'],
            'status' => 'active',
            'started_at' => now(),
        ]);

        Flux::toast(variant: 'success', text: __('Estudiante reactivado en tu institución.'));
        $this->reset(['document_type', 'document_number', 'name', 'email', 'group_id']);
    }

    protected function enrollTeacher(array $data): void
    {
        $institutionId = Auth::user()->institution_id;

        $user = User::where('document_type', $data['document_type'])
            ->where('document_number', $data['document_number'])
            ->first();

        if (! $user) {
            DB::transaction(function () use ($data, $institutionId, &$user) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'] ?: "{$data['document_number']}@teachers.tereto.local",
                    'password' => Hash::make(Str::random(32)),
                    'document_type' => $data['document_type'],
                    'document_number' => $data['document_number'],
                ]);
                $user->assignRole('teacher');

                $membership = InstitutionMembership::create([
                    'user_id' => $user->id,
                    'institution_id' => $institutionId,
                    'status' => 'active',
                    'started_at' => now(),
                ]);

                if ($data['group_id']) {
                    $user->teacherGroups()->attach($data['group_id'], ['institution_membership_id' => $membership->id]);
                }
            });

            Flux::toast(variant: 'success', text: __('Profesor matriculado.'));
            $this->reset(['document_type', 'document_number', 'name', 'email', 'group_id']);

            return;
        }

        $activeMembership = $user->activeMembership()->first();

        if ($activeMembership) {
            if ($activeMembership->institution_id !== $institutionId) {
                Flux::toast(variant: 'danger', text: __('Ya está matriculado activamente en otra institución.'));
            } else {
                Flux::toast(variant: 'danger', text: __('Ya está matriculado activamente en tu institución.'));
            }

            return;
        }

        DB::transaction(function () use ($data, $institutionId, $user) {
            $membership = InstitutionMembership::create([
                'user_id' => $user->id,
                'institution_id' => $institutionId,
                'status' => 'active',
                'started_at' => now(),
            ]);

            if ($data['group_id']) {
                $user->teacherGroups()->attach($data['group_id'], ['institution_membership_id' => $membership->id]);
            }
        });

        Flux::toast(variant: 'success', text: __('Profesor reactivado en tu institución.'));
        $this->reset(['document_type', 'document_number', 'name', 'email', 'group_id']);
    }

    protected function linkGuardian(array $data): void
    {
        $guardian = User::where('document_type', $data['document_type'])
            ->where('document_number', $data['document_number'])
            ->first();

        DB::transaction(function () use ($data, &$guardian) {
            if (! $guardian) {
                $guardian = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'] ?: "{$data['document_number']}@guardians.tereto.local",
                    'password' => Hash::make(Str::random(32)),
                    'document_type' => $data['document_type'],
                    'document_number' => $data['document_number'],
                ]);
                $guardian->assignRole('guardian');
            }

            $studentId = Student::where('uuid', $data['existing_student_uuid'])->value('id');
            $guardian->guardianStudents()->syncWithoutDetaching([$studentId]);
        });

        Flux::toast(variant: 'success', text: __('Acudiente vinculado al estudiante.'));
        $this->reset(['document_type', 'document_number', 'name', 'email', 'existing_student_uuid']);
    }

    public function render()
    {
        return view('livewire.actors.enroll-actor');
    }
}
