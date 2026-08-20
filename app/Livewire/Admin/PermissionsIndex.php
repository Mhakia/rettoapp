<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

#[Title('Permisos')]
class PermissionsIndex extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::user()->hasRole('super_admin'), 403);
    }

    public function render()
    {
        $permissions = Permission::with('roles')->orderBy('name')->get();

        return view('livewire.admin.permissions-index', ['permissions' => $permissions]);
    }
}
