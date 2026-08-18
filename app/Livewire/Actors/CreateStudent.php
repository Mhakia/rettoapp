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
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Crear estudiante')]
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

    public string $first_name = '';

    public string $last_name = '';

    public string $document_type = '';

    public string $document_number = '';

    public string $birth_date = '';

    public ?int $group_id = null;

    public function mount(): void
    {
        $institution = $this->institutionUuid
            ? Institution::where('uuid', $this->institutionUuid)->firstOrFail()
            : Auth::user()->institution;

        abort_unless($institution, 403);
        $this->authorize('manageActors', $institution);

        $this->institutionId = $institution->id;
        $this->institutionName = $institution->name;
        $this->institutionUuid = $institution->uuid;

        $this->backUrl = Auth::user()->hasRole('institution_admin')
            ? route('actors.roster')
            : route('institutions.show', ['institution' => $institution->uuid, 'tab' => 'student']);
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

        $data = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', Rule::in(['registro_civil', 'tarjeta_identidad'])],
            'document_number' => ['required', 'string', 'max:255', Rule::unique('students', 'document_number')->where('document_type', $this->document_type)],
            'birth_date' => ['required', 'date', 'before:today'],
            'group_id' => ['required', 'exists:groups,id,institution_id,'.$this->institutionId],
        ]);

        $institutionId = $this->institutionId;
        $name = trim("{$data['first_name']} {$data['last_name']}");

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
