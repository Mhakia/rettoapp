<?php

namespace App\Livewire\Actors;

use App\Models\Institution;
use App\Models\Student;
use App\Models\User;
use App\Notifications\GuardianAccountCreated;
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

#[Title('Crear acudiente')]
class CreateGuardian extends Component
{
    /**
     * Institution to link students from: the query string is used by staff (pedagogue/manager/
     * super_admin) browsing a specific institution; institution_admin always uses their own, ignoring it.
     */
    #[Url(as: 'institution')]
    public ?string $institutionUuid = null;

    public int $institutionId;

    public string $institutionName = '';

    /**
     * Where "volver"/"cancelar" and the post-save redirect should go, depending on who is creating the guardian.
     */
    public string $backUrl = '';

    public string $first_name = '';

    public string $last_name = '';

    public string $document_type = '';

    public string $document_number = '';

    public string $birth_date = '';

    public string $address = '';

    public string $phone = '';

    public string $email = '';

    public string $studentSearch = '';

    /**
     * @var array<int, int>
     */
    public array $student_ids = [];

    /**
     * Data of a guardian already registered elsewhere with the same document (readonly, cannot be edited here):
     * a guardian can have children in more than one institution, so we only let this institution link students.
     *
     * @var array<string, mixed>|null
     */
    public ?array $existingGuardian = null;

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
            ? route('actors.guardians.index')
            : route('institutions.show', ['institution' => $institution->uuid, 'tab' => 'student']);
    }

    public function updatedDocumentType(): void
    {
        $this->checkExistingGuardian();
    }

    public function updatedDocumentNumber(): void
    {
        $this->checkExistingGuardian();
    }

    protected function checkExistingGuardian(): void
    {
        if ($this->document_type === '' || $this->document_number === '') {
            $this->existingGuardian = null;

            return;
        }

        $guardian = User::role('guardian')
            ->where('document_type', $this->document_type)
            ->where('document_number', $this->document_number)
            ->first();

        $this->existingGuardian = $guardian ? [
            'id' => $guardian->id,
            'name' => $guardian->name,
            'email' => $guardian->email,
            'phone' => $guardian->phone,
            'address' => $guardian->address,
            'birth_date' => $guardian->birth_date?->format('d/m/Y'),
            'linked_student_ids' => $guardian->guardianStudents()->pluck('students.id')->all(),
        ] : null;
    }

    /**
     * Active students of this institution, excluding whoever the matched guardian already has linked.
     */
    #[Computed]
    public function students()
    {
        $search = trim($this->studentSearch);
        $alreadyLinkedIds = $this->existingGuardian['linked_student_ids'] ?? [];

        return Student::with('user')
            ->whereHas('activeMembership', fn ($q) => $q->where('institution_id', $this->institutionId))
            ->when($alreadyLinkedIds !== [], fn ($q) => $q->whereNotIn('id', $alreadyLinkedIds))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->whereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$search}%"))
                        ->orWhere('document_number', 'ilike', "%{$search}%");
                });
            })
            ->orderBy(User::select('name')->whereColumn('users.id', 'students.user_id'))
            ->limit(50)
            ->get();
    }

    public function store(): void
    {
        $rules = [
            'document_type' => ['required', Rule::in(Institution::DOCUMENT_TYPES)],
            'document_number' => ['required', 'string', 'max:255'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ];

        if (! $this->existingGuardian) {
            $rules += [
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'document_number' => ['required', 'string', 'max:255', Rule::unique('users', 'document_number')->where('document_type', $this->document_type)],
                'birth_date' => ['required', 'date', 'before:today'],
                'address' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            ];
        }

        $data = $this->validate($rules);

        // Re-check server-side that every chosen student actually belongs to this institution
        // and isn't already linked, regardless of what the client sent.
        $alreadyLinkedIds = $this->existingGuardian['linked_student_ids'] ?? [];
        $validStudentIds = Student::whereIn('id', $data['student_ids'])
            ->whereHas('activeMembership', fn ($q) => $q->where('institution_id', $this->institutionId))
            ->when($alreadyLinkedIds !== [], fn ($q) => $q->whereNotIn('id', $alreadyLinkedIds))
            ->pluck('id')
            ->all();

        abort_unless(count($validStudentIds) === count($data['student_ids']), 422);

        if ($this->existingGuardian) {
            $guardian = User::findOrFail($this->existingGuardian['id']);

            $guardian->guardianStudents()->syncWithoutDetaching($validStudentIds);

            Flux::toast(variant: 'success', text: __('Acudiente ya registrado. Se vincularon los estudiantes seleccionados.'));
            $this->redirect($this->backUrl, navigate: true);

            return;
        }

        $name = trim("{$data['first_name']} {$data['last_name']}");

        $guardian = DB::transaction(function () use ($data, $name, $validStudentIds) {
            $guardian = User::create([
                'name' => $name,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'document_type' => $data['document_type'],
                'document_number' => $data['document_number'],
                'birth_date' => $data['birth_date'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'password' => Hash::make(Str::random(32)),
            ]);
            $guardian->assignRole('guardian');

            $guardian->guardianStudents()->attach($validStudentIds);

            return $guardian;
        });

        // Dispatched after the transaction commits so the queued job always finds the row.
        $guardian->notify(new GuardianAccountCreated);

        Flux::toast(variant: 'success', text: __('Acudiente creado. Se envió un correo de activación.'));

        $this->redirect($this->backUrl, navigate: true);
    }

    public function render()
    {
        return view('livewire.actors.create-guardian');
    }
}
