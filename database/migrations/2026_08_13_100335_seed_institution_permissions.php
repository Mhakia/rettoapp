<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view-institutions',
            'create-institution',
            'update-institution',
            'delete-institution',
            'assign-institution-admin',
            'update-challenge',
            'delete-challenge',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findByName('super_admin')->givePermissionTo([
            'view-institutions',
            'create-institution',
            'update-institution',
            'delete-institution',
            'assign-institution-admin',
            'create-challenge',
            'update-challenge',
            'delete-challenge',
        ]);

        Role::findByName('manager')->givePermissionTo([
            'view-institutions',
            'create-institution',
            'update-institution',
            'assign-institution-admin',
        ]);

        Role::findByName('pedagogue')->givePermissionTo([
            'view-institutions',
            'update-challenge',
            'delete-challenge',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('guard_name', 'web')->whereIn('name', [
            'view-institutions',
            'create-institution',
            'update-institution',
            'delete-institution',
            'assign-institution-admin',
            'update-challenge',
            'delete-challenge',
        ])->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
