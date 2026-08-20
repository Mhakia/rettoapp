<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Policies\UserPolicy;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Usuarios internos')]
class UsersIndex extends Component
{
    use WithPagination;

    public string $search = '';

    #[Url]
    public string $role = '';

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    /**
     * Keyset (cursor) pagination stays fast no matter how many internal users exist.
     */
    #[Computed]
    public function users()
    {
        $search = trim($this->search);
        $roles = $this->role !== '' ? [$this->role] : UserPolicy::INTERNAL_ROLES;

        return User::role($roles)
            ->with('roles')
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")->orWhere('email', 'ilike', "%{$search}%");
            }))
            ->orderByDesc('id')
            ->cursorPaginate(10);
    }

    /**
     * A key that changes whenever the current page's set of users changes, used to force
     * Alpine to reinitialize with fresh `userDetails` instead of keeping stale client state.
     */
    #[Computed]
    public function usersCacheKey(): string
    {
        return md5($this->users->pluck('uuid')->implode(','));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    #[Computed]
    public function userDetails(): array
    {
        return $this->users->getCollection()->mapWithKeys(fn (User $user) => [
            $user->uuid => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->pluck('name')->implode(', '),
                'createdAt' => $user->created_at?->format('d/m/Y'),
                'verified' => $user->email_verified_at !== null,
            ],
        ])->all();
    }

    public function deactivate(string $uuid): void
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        $this->authorize('delete', $user);

        $user->forceFill(['deleted_by' => Auth::id()])->save();
        $user->delete();

        unset($this->users, $this->usersCacheKey, $this->userDetails);

        Flux::toast(variant: 'success', text: __('Usuario desactivado.'));
    }

    public function render()
    {
        return view('livewire.admin.users-index');
    }
}
