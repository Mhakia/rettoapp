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
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Profesor')]
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

    /**
     * Teacher being edited; null means this form is creating a new one.
     */
    #[Locked]
    public ?int $editingId = null;

    /**
     * Active membership id being edited, so store() knows which one to sync groups against.
     */
    #[Locked]
    public ?int $membershipId = null;

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

    public function mount(?User $teacher = null): void
    {
        $activeMembership = $teacher?->activeMembership()->first();

        if ($teacher) {
            abort_unless($teacher->hasRole('teacher') && $activeMembership, 404);
        }

        $institution = $teacher
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
            ? route('actors.teachers.index')
            : route('institutions.show', ['institution' => $institution->uuid, 'tab' => 'teacher']);

        if ($teacher) {
            $this->editingId = $teacher->id;
            $this->membershipId = $activeMembership->id;
            $this->first_name = $teacher->first_name ?? '';
            $this->last_name = $teacher->last_name ?? '';
            $this->document_type = $teacher->document_type ?? '';
            $this->document_number = $teacher->document_number ?? '';
            $this->phone = $teacher->phone ?? '';
            $this->email = $teacher->email;
            $this->group_ids = $teacher->teacherGroups()
                ->wherePivot('institution_membership_id', $activeMembership->id)
                ->pluck('groups.id')
                ->all();
        }
    }

    #[Computed]
    public function groups()
    {
        return Group::where('institution_id', $this->institutionId)->orderBy('name')->get();
    }

    public function store(): void
    {
        $documentRule = Rule::unique('users', 'document_number')->where('document_type', $this->document_type);
        $emailRule = Rule::unique('users', 'email');

        if ($this->editingId) {
            $documentRule->ignore($this->editingId);
            $emailRule->ignore($this->editingId);
        }

        $data = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', Rule::in(Institution::DOCUMENT_TYPES)],
            'document_number' => ['required', 'string', 'max:255', $documentRule],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'group_ids' => ['array'],
            'group_ids.*' => ['integer', 'exists:groups,id,institution_id,'.$this->institutionId],
        ]);

        $name = trim("{$data['first_name']} {$data['last_name']}");

        if ($this->editingId) {
            $teacher = User::findOrFail($this->editingId);
            $membershipId = $this->membershipId;

            DB::transaction(function () use ($data, $name, $teacher, $membershipId) {
                $teacher->update([
                    'name' => $name,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'document_type' => $data['document_type'],
                    'document_number' => $data['document_number'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                ]);

                $currentIds = $teacher->teacherGroups()
                    ->wherePivot('institution_membership_id', $membershipId)
                    ->pluck('groups.id')
                    ->all();

                $toAttach = array_values(array_diff($data['group_ids'], $currentIds));
                $toDetach = array_values(array_diff($currentIds, $data['group_ids']));

                if ($toDetach !== []) {
                    $teacher->teacherGroups()->wherePivot('institution_membership_id', $membershipId)->detach($toDetach);
                }

                if ($toAttach !== []) {
                    $teacher->teacherGroups()->attach(
                        collect($toAttach)->mapWithKeys(fn ($id) => [$id => ['institution_membership_id' => $membershipId]])->all()
                    );
                }
            });

            Flux::toast(variant: 'success', text: __('Profesor actualizado.'));

            $this->redirect($this->backUrl, navigate: true);

            return;
        }

        $institutionId = $this->institutionId;

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
