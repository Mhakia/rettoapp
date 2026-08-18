<?php

namespace App\Livewire\Actors;

use App\Models\Group;
use App\Models\Institution;
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
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Estudiante')]
class CreateStudent extends Component
{
    /**
     * Institution to enroll the student into: the query string is used by staff (pedagogue/manager/
     * super_admin) browsing a specific institution; institution_admin always uses their own, ignoring it.
     */
    #[Url(as: 'institution')]
    public ?string $institutionUuid = null;

    public int $institutionId;

    public string $institutionName = '';

    /**
     * Where "volver"/"cancelar" and the post-save redirect should go, depending on who is creating the student.
     */
    public string $backUrl = '';

    /**
     * Student being edited; null means this form is creating a new one.
     */
    #[Locked]
    public ?int $editingId = null;

    /**
     * Active membership id being edited, so store() knows which one to update.
     */
    #[Locked]
    public ?int $membershipId = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $document_type = '';

    public string $document_number = '';

    public string $birth_date = '';

    public ?int $group_id = null;

    public function mount(?Student $student = null): void
    {
        $activeMembership = $student?->activeMembership;

        if ($student) {
            abort_unless($activeMembership, 404);
        }

        $institution = $student
            ? Institution::findOrFail($activeMembership->institution_id)
            : ($this->institutionUuid
                ? Institution::where('uuid', $this->institutionUuid)->firstOrFail()
                : Auth::user()->institution);

        abort_unless($institution, 403);
        $this->authorize('manageActors', $institution);

        $this->institutionId = $institution->id;
        $this->institutionName = $institution->name;
        $this->institutionUuid = $institution->uuid;

        $this->backUrl = Auth::user()->hasRole('institution_admin')
            ? route('actors.students.index')
            : route('institutions.show', ['institution' => $institution->uuid, 'tab' => 'student']);

        if ($student) {
            $this->editingId = $student->id;
            $this->membershipId = $activeMembership->id;
            $this->first_name = $student->user->first_name ?? '';
            $this->last_name = $student->user->last_name ?? '';
            $this->document_type = $student->document_type;
            $this->document_number = $student->document_number;
            $this->birth_date = $student->birth_date?->format('Y-m-d') ?? '';
            $this->group_id = $activeMembership->group_id;
        }
    }

    #[Computed]
    public function groups()
    {
        return Group::where('institution_id', $this->institutionId)->orderBy('name')->get();
    }

    public function store(): void
    {
        $institution = Institution::findOrFail($this->institutionId);
        $this->authorize('manageActors', $institution);

        $documentRule = Rule::unique('students', 'document_number')->where('document_type', $this->document_type);

        if ($this->editingId) {
            $documentRule->ignore($this->editingId);
        }

        $data = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', Rule::in(['registro_civil', 'tarjeta_identidad'])],
            'document_number' => ['required', 'string', 'max:255', $documentRule],
            'birth_date' => ['required', 'date', 'before:today'],
            'group_id' => ['required', 'exists:groups,id,institution_id,'.$this->institutionId],
        ]);

        $name = trim("{$data['first_name']} {$data['last_name']}");

        if ($this->editingId) {
            $student = Student::findOrFail($this->editingId);

            DB::transaction(function () use ($data, $name, $student) {
                $student->user->update([
                    'name' => $name,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                ]);

                $student->update([
                    'document_type' => $data['document_type'],
                    'document_number' => $data['document_number'],
                    'birth_date' => $data['birth_date'],
                ]);

                InstitutionMembership::whereKey($this->membershipId)->update(['group_id' => $data['group_id']]);
            });

            Flux::toast(variant: 'success', text: __('Estudiante actualizado.'));

            $this->redirect($this->backUrl, navigate: true);

            return;
        }

        $institutionId = $this->institutionId;

        DB::transaction(function () use ($data, $name, $institutionId) {
            $user = User::create([
                'name' => $name,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                // No login flow yet for students: placeholder email so the account can be wired up later.
                // Prefixed with document_type too: document_number alone isn't unique across types (only the type+number pair is).
                'email' => "{$data['document_type']}-{$data['document_number']}@students.tereto.local",
                'password' => Hash::make(Str::random(32)),
            ]);
            $user->assignRole('student');

            $student = Student::create([
                'user_id' => $user->id,
                'document_type' => $data['document_type'],
                'document_number' => $data['document_number'],
                'birth_date' => $data['birth_date'],
            ]);

            InstitutionMembership::create([
                'user_id' => $student->user_id,
                'institution_id' => $institutionId,
                'group_id' => $data['group_id'],
                'status' => 'active',
                'started_at' => now(),
            ]);
        });

        Flux::toast(variant: 'success', text: __('Estudiante creado.'));

        $this->redirect($this->backUrl, navigate: true);
    }

    public function render()
    {
        return view('livewire.actors.create-student');
    }
}
