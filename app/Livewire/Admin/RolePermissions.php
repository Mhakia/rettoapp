<?php

namespace App\Livewire\Admin;

use App\Services\RoleManager;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Title('Permisos del rol')]
class RolePermissions extends Component
{
    /**
     * Verb prefixes stripped when grouping permissions, since Spatie has no native categories.
     *
     * @var array<int, string>
     */
    private const VERBS = ['view', 'create', 'update', 'delete', 'manage', 'assign', 'verify', 'complete'];

    #[Locked]
    public int $roleId;

    public string $roleName = '';

    /**
     * @var array<int, string>
     */
    public array $permission_names = [];

    /**
     * super_admin's own permissions can't be edited from this screen, to avoid an accidental self-lockout.
     */
    #[Locked]
    public bool $isSuperAdminRole = false;

    public function mount(Role $role): void
    {
        abort_unless(Auth::user()->hasRole('super_admin'), 403);

        $this->roleId = $role->id;
        $this->roleName = $role->name;
        $this->isSuperAdminRole = $role->name === 'super_admin';
        $this->permission_names = $role->permissions()->pluck('name')->all();
    }

    /**
     * @return Collection<string, Collection<int, Permission>>
     */
    #[Computed]
    public function groupedPermissions()
    {
        return Permission::orderBy('name')->get()->groupBy(function (Permission $permission) {
            $parts = explode('-', $permission->name);

            if (in_array($parts[0], self::VERBS, true)) {
                array_shift($parts);
            }

            return $parts === [] ? 'general' : implode('-', $parts);
        });
    }

    public function save(): void
    {
        abort_if($this->isSuperAdminRole, 403);

        $role = Role::findOrFail($this->roleId);

        app(RoleManager::class)->syncPermissions($role, $this->permission_names, Auth::user());

        Flux::toast(variant: 'success', text: __('Permisos actualizados.'));
        $this->redirect(route('admin.roles.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.role-permissions');
    }
}
