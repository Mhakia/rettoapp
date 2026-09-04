<?php

namespace App\Livewire\Institutions;

use App\Concerns\InstitutionValidationRules;
use App\Models\Institution;
use App\Models\User;
use App\Notifications\InstitutionAdminAccountCreated;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Instituciones')]
class Index extends Component
{
    use InstitutionValidationRules, WithPagination;

    public string $search = '';

    public ?string $editingUuid = null;

    public string $name = '';

    public string $nit = '';

    public string $address = '';

    public string $phone = '';

    public string $bulletin_frequency = 'disabled';

    public string $contact_first_name = '';

    public string $contact_middle_name = '';

    public string $contact_last_name = '';

    public string $contact_second_last_name = '';

    public string $contact_document_type = '';

    public string $contact_document_number = '';

    public string $contact_email = '';

    public string $contact_phone = '';

    public string $principal_name = '';

    public string $principal_document_type = '';

    public string $principal_document_number = '';

    public ?string $principal_started_at = null;

    public string $country = '';

    public string $state = '';

    public string $city = '';

    public string $entity_type = '';

    public ?string $assigningUuid = null;

    public string $admin_name = '';

    public string $admin_email = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Institution::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Keyset (cursor) pagination stays fast no matter how many institutions exist.
     */
    #[Computed]
    public function institutions()
    {
        $search = trim($this->search);

        return Institution::withCount(['branches', 'groups'])
            ->with('admin')
            ->withCount([
                'memberships as active_student_count' => fn ($query) => $query->where('status', 'active')->whereHas('user', fn ($u) => $u->role('student')),
                'memberships as active_teacher_count' => fn ($query) => $query->where('status', 'active')->whereHas('user', fn ($u) => $u->role('teacher')),
            ])
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                // ILIKE (case-insensitive) is accelerated by the pg_trgm GIN indexes on these columns.
                $q->where('name', 'ilike', "%{$search}%")->orWhere('nit', 'ilike', "%{$search}%");
            }))
            ->orderByDesc('id')
            ->cursorPaginate(10);
    }

    /**
     * A key that changes whenever the current page's set of institutions changes, used to force
     * Alpine to reinitialize with fresh `institutionDetails` instead of keeping stale client state.
     */
    #[Computed]
    public function institutionsCacheKey(): string
    {
        return md5($this->institutions->pluck('uuid')->implode(','));
    }

    /**
     * Full detail for every institution on the current page, embedded once so the popup opens
     * instantly client-side with zero extra requests.
     *
     * @return array<string, array<string, mixed>>
     */
    #[Computed]
    public function institutionDetails(): array
    {
        return $this->institutions->getCollection()->mapWithKeys(fn (Institution $institution) => [
            $institution->uuid => [
                'name' => $institution->name,
                'nit' => $institution->nit,
                'address' => $institution->address,
                'phone' => $institution->phone,
                'bulletin_frequency' => $institution->bulletin_frequency,
                'entity_type' => $institution->entity_type,
                'country' => $institution->country,
                'state' => $institution->state,
                'city' => $institution->city,
                'contact_name' => trim(collect([
                    $institution->contact_first_name,
                    $institution->contact_middle_name,
                    $institution->contact_last_name,
                    $institution->contact_second_last_name,
                ])->filter()->implode(' ')),
                'contact_document' => trim(collect([$institution->contact_document_type, $institution->contact_document_number])->filter()->implode(' ')),
                'contact_email' => $institution->contact_email,
                'contact_phone' => $institution->contact_phone,
                'principal_name' => $institution->principal_name,
                'principal_document' => trim(collect([$institution->principal_document_type, $institution->principal_document_number])->filter()->implode(' ')),
                'principal_started_at' => $institution->principal_started_at?->format('d/m/Y'),
                'admin_name' => $institution->admin?->name,
                'admin_email' => $institution->admin?->email,
                'active_student_count' => $institution->active_student_count,
                'active_teacher_count' => $institution->active_teacher_count,
                'groups_count' => $institution->groups_count,
            ],
        ])->all();
    }

    public function edit(string $uuid): void
    {
        $institution = Institution::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $institution);

        $this->editingUuid = $institution->uuid;
        $this->name = $institution->name;
        $this->nit = (string) $institution->nit;
        $this->address = (string) $institution->address;
        $this->phone = (string) $institution->phone;
        $this->bulletin_frequency = $institution->bulletin_frequency;
        $this->contact_first_name = (string) $institution->contact_first_name;
        $this->contact_middle_name = (string) $institution->contact_middle_name;
        $this->contact_last_name = (string) $institution->contact_last_name;
        $this->contact_second_last_name = (string) $institution->contact_second_last_name;
        $this->contact_document_type = (string) $institution->contact_document_type;
        $this->contact_document_number = (string) $institution->contact_document_number;
        $this->contact_email = (string) $institution->contact_email;
        $this->contact_phone = (string) $institution->contact_phone;
        $this->principal_name = (string) $institution->principal_name;
        $this->principal_document_type = (string) $institution->principal_document_type;
        $this->principal_document_number = (string) $institution->principal_document_number;
        $this->principal_started_at = $institution->principal_started_at?->toDateString();
        $this->country = (string) $institution->country;
        $this->state = (string) $institution->state;
        $this->city = (string) $institution->city;
        $this->entity_type = (string) $institution->entity_type;
    }

    public function save(): void
    {
        $institution = Institution::where('uuid', $this->editingUuid)->firstOrFail();
        $this->authorize('update', $institution);

        $data = $this->validate($this->institutionRules($institution->id));

        $institution->update($data);

        $this->reset([
            'editingUuid',
            'name',
            'nit',
            'address',
            'phone',
            'bulletin_frequency',
            'contact_first_name',
            'contact_middle_name',
            'contact_last_name',
            'contact_second_last_name',
            'contact_document_type',
            'contact_document_number',
            'contact_email',
            'contact_phone',
            'principal_name',
            'principal_document_type',
            'principal_document_number',
            'principal_started_at',
            'country',
            'state',
            'city',
            'entity_type',
        ]);
        unset($this->institutions, $this->institutionsCacheKey, $this->institutionDetails);

        Flux::toast(variant: 'success', text: __('institution_updated'));
    }

    public function delete(string $uuid): void
    {
        $institution = Institution::where('uuid', $uuid)->firstOrFail();
        $this->authorize('delete', $institution);

        $institution->forceFill(['deleted_by' => auth()->id()])->save();
        $institution->delete();
        unset($this->institutions, $this->institutionsCacheKey, $this->institutionDetails);

        Flux::toast(variant: 'success', text: __('institution_deleted'));
    }

    public function assign(string $uuid): void
    {
        $institution = Institution::where('uuid', $uuid)->firstOrFail();
        $this->authorize('assignAdmin', $institution);

        $this->assigningUuid = $institution->uuid;
        $this->admin_name = '';
        $this->admin_email = '';
    }

    public function saveAdmin(): void
    {
        $institution = Institution::where('uuid', $this->assigningUuid)->firstOrFail();
        $this->authorize('assignAdmin', $institution);

        $data = $this->validate([
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $admin = User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'institution_id' => $institution->id,
            'password' => Hash::make(Str::random(32)),
        ]);
        $admin->assignRole('institution_admin');
        $admin->notify(new InstitutionAdminAccountCreated);

        $this->reset(['assigningUuid', 'admin_name', 'admin_email']);
        unset($this->institutions, $this->institutionsCacheKey, $this->institutionDetails);

        Flux::toast(variant: 'success', text: __('Admin asignado a la institución.'));
    }

    public function render()
    {
        return view('livewire.institutions.index');
    }
}
