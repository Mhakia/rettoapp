<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Roles')]
class RolesIndex extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::user()->hasRole('super_admin'), 403);
    }

    public function render()
    {
        $roles = Role::withCount(['users', 'permissions'])->orderBy('name')->get();

        return view('livewire.admin.roles-index', ['roles' => $roles]);
    }
}
