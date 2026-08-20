<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Notifications\InternalUserAccountCreated;
use App\Policies\UserPolicy;
use App\Services\RoleManager;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Usuario interno')]
class CreateInternalUser extends Component
{
    /**
     * User being edited; null means this form is creating a new one.
     */
    #[Locked]
    public ?int $editingId = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $role = '';

    public function mount(?User $user = null): void
    {
        $this->authorize($user ? 'update' : 'create', $user ?? User::class);

        if ($user) {
            $this->editingId = $user->id;
            $this->first_name = $user->first_name ?? '';
            $this->last_name = $user->last_name ?? '';
            $this->email = $user->email;
            $this->role = $user->roles()->whereIn('name', UserPolicy::INTERNAL_ROLES)->value('name') ?? '';
        }
    }

    public function store(): void
    {
        $emailRule = Rule::unique('users', 'email');

        if ($this->editingId) {
            $emailRule->ignore($this->editingId);
        }

        $data = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'role' => ['required', Rule::in(UserPolicy::INTERNAL_ROLES)],
        ]);

        $name = trim("{$data['first_name']} {$data['last_name']}");
        $actor = Auth::user();

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $this->authorize('update', $user);

            $user->update([
                'name' => $name,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
            ]);

            app(RoleManager::class)->syncRoles($user, [$data['role']], $actor);

            Flux::toast(variant: 'success', text: __('Usuario actualizado.'));
            $this->redirect(route('admin.users.index'), navigate: true);

            return;
        }

        $user = DB::transaction(function () use ($data, $name, $actor) {
            $user = User::create([
                'name' => $name,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make(Str::random(32)),
            ]);

            app(RoleManager::class)->syncRoles($user, [$data['role']], $actor);

            return $user;
        });

        // Sent after the transaction commits so the queued job always finds the row.
        $user->notify(new InternalUserAccountCreated);

        Flux::toast(variant: 'success', text: __('Usuario creado. Se envió un correo de activación.'));
        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.create-internal-user');
    }
}
