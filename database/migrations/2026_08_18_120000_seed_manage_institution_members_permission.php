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

        Permission::findOrCreate('manage-institution-members');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Pedagogue is intentionally excluded: it's focused on challenges (results/creation), not on managing teachers/students.
        Role::findByName('super_admin')->givePermissionTo('manage-institution-members');
        Role::findByName('manager')->givePermissionTo('manage-institution-members');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('guard_name', 'web')->where('name', 'manage-institution-members')->delete();
    }
};
