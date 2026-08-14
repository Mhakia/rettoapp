<?php

namespace App\Livewire\Institutions;

use App\Models\Institution;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Instituciones')]
class Index extends Component
{
    public ?string $editingUuid = null;

    public string $name = '';

    public string $nit = '';

    public string $address = '';

    public string $phone = '';

    public string $bulletin_frequency = 'disabled';

    public ?string $assigningUuid = null;

    public string $admin_name = '';

    public string $admin_email = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Institution::class);
    }

    #[Computed]
    public function institutions()
    {
        return Institution::withCount(['branches', 'groups'])
            ->with('admin')
            ->withCount([
                'memberships as active_student_count' => fn ($query) => $query->where('status', 'active')->whereHas('user', fn ($u) => $u->role('student')),
                'memberships as active_teacher_count' => fn ($query) => $query->where('status', 'active')->whereHas('user', fn ($u) => $u->role('teacher')),
            ])
            ->orderBy('name')
            ->get();
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
    }

    public function save(): void
    {
        $institution = Institution::where('uuid', $this->editingUuid)->firstOrFail();
        $this->authorize('update', $institution);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'nit' => ['nullable', 'string', 'max:255', Rule::unique('institutions', 'nit')->ignore($institution->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'bulletin_frequency' => ['required', Rule::in(['weekly', 'biweekly', 'monthly', 'disabled'])],
        ]);

        $institution->update($data);

        $this->reset(['editingUuid', 'name', 'nit', 'address', 'phone', 'bulletin_frequency']);
        unset($this->institutions);

        Flux::toast(variant: 'success', text: __('Institución actualizada.'));
    }

    public function delete(string $uuid): void
    {
        $institution = Institution::where('uuid', $uuid)->firstOrFail();
        $this->authorize('delete', $institution);

        $institution->delete();
        unset($this->institutions);

        Flux::toast(variant: 'success', text: __('Institución eliminada.'));
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

        $this->reset(['assigningUuid', 'admin_name', 'admin_email']);
        unset($this->institutions);

        Flux::toast(variant: 'success', text: __('Admin asignado a la institución.'));
    }

    public function render()
    {
        return view('livewire.institutions.index');
    }
}
