<?php

namespace App\Livewire\Actors;

use App\Models\Group;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\User;
use App\Notifications\TeacherAccountCreated;
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

#[Title('Crear profesor')]
class CreateTeacher extends Component
{
    /**
     * Institution to enroll the teacher into: the query string is used by staff (pedagogue/manager/
     * super_admin) browsing a specific institution; institution_admin always uses their own, ignoring it.
     */
    #[Url(as: 'institution')]
    public ?string $institutionUuid = null;

    public int $institutionId;

    public string $institutionName = '';

    /**
     * Where "volver"/"cancelar" and the post-save redirect should go, depending on who is creating the teacher.
     */
    public string $backUrl = '';

    public string $first_name = '';

    public string $last_name = '';

    public string $document_type = '';

    public string $document_number = '';

    public string $phone = '';

    public string $email = '';

    /**
     * @var array<int, int>
     */
    public array $group_ids = [];

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
            : route('institutions.show', ['institution' => $institution->uuid, 'tab' => 'teacher']);
    }

    #[Computed]
    public function groups()
    {
        return Group::where('institution_id', $this->institutionId)->orderBy('name')->get();
    }

    public function store(): void
    {
        $data = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', Rule::in(Institution::DOCUMENT_TYPES)],
            'document_number' => ['required', 'string', 'max:255', Rule::unique('users', 'document_number')->where('document_type', $this->document_type)],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'group_ids' => ['array'],
            'group_ids.*' => ['integer', 'exists:groups,id,institution_id,'.$this->institutionId],
        ]);

        $institutionId = $this->institutionId;
        $name = trim("{$data['first_name']} {$data['last_name']}");

        $teacher = DB::transaction(function () use ($data, $name, $institutionId) {
            $teacher = User::create([
                'name' => $name,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'document_type' => $data['document_type'],
                'document_number' => $data['document_number'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'password' => Hash::make(Str::random(32)),
            ]);
            $teacher->assignRole('teacher');

            $membership = InstitutionMembership::create([
                'user_id' => $teacher->id,
                'institution_id' => $institutionId,
                'status' => 'active',
                'started_at' => now(),
            ]);

            if (! empty($data['group_ids'])) {
                $teacher->teacherGroups()->attach($data['group_ids'], ['institution_membership_id' => $membership->id]);
            }

            return $teacher;
        });

        // Dispatched after the transaction commits so the queued job always finds the row.
        $teacher->notify(new TeacherAccountCreated);

        Flux::toast(variant: 'success', text: __('Profesor creado. Se envió un correo de activación.'));

        $this->redirect($this->backUrl, navigate: true);
    }

    public function render()
    {
        return view('livewire.actors.create-teacher');
    }
}
