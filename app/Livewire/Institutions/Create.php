<?php

namespace App\Livewire\Institutions;

use App\Concerns\InstitutionValidationRules;
use App\Models\Institution;
use App\Models\User;
use App\Notifications\InstitutionAdminAccountCreated;
use Flux\Flux;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Crear institución')]
class Create extends Component
{
    use InstitutionValidationRules;

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

    public string $admin_name = '';

    public string $admin_email = '';

    public function mount(): void
    {
        $this->authorize('create', Institution::class);
    }

    public function store(): void
    {
        $this->authorize('create', Institution::class);

        $data = $this->validate([
            ...$this->institutionRules(),
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        DB::transaction(function () use ($data) {
            $institution = Institution::create(Arr::except($data, ['admin_name', 'admin_email']));

            $admin = User::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'institution_id' => $institution->id,
                'password' => Hash::make(Str::random(32)),
            ]);
            $admin->assignRole('institution_admin');
            $admin->notify(new InstitutionAdminAccountCreated);
        });

        Flux::toast(variant: 'success', text: __('Institución creada. Se envió un correo de activación al administrador.'));

        $this->redirectRoute('institutions.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.institutions.create');
    }
}
