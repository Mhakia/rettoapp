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

        $roles = [
            'super_admin',
            'manager',
            'pedagogue',
            'institution_admin',
            'teacher',
            'guardian',
            'student',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role);
        }

        $permissions = [
            'create-challenge',
            'view-challenge-statistics',
            'complete-challenge',
            'verify-challenge',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // The in-memory permissions cache was warmed before these rows existed; force a reload.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findByName('pedagogue')->givePermissionTo(['create-challenge', 'view-challenge-statistics']);
        Role::findByName('super_admin')->givePermissionTo(['view-challenge-statistics']);
        Role::findByName('student')->givePermissionTo('complete-challenge');
        Role::findByName('teacher')->givePermissionTo(['complete-challenge', 'verify-challenge']);
        Role::findByName('guardian')->givePermissionTo('complete-challenge');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('guard_name', 'web')->whereIn('name', [
            'create-challenge',
            'view-challenge-statistics',
            'complete-challenge',
            'verify-challenge',
        ])->delete();

        Role::where('guard_name', 'web')->whereIn('name', [
            'super_admin',
            'manager',
            'pedagogue',
            'institution_admin',
            'teacher',
            'guardian',
            'student',
        ])->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
