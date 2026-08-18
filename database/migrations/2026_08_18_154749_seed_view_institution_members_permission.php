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

        Permission::findOrCreate('view-institution-members');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Read-only directory access across all institutions. Separate from 'manage-institution-members'
        // (create/edit/withdraw) so pedagogue can browse without being able to modify anything.
        Role::findByName('super_admin')->givePermissionTo('view-institution-members');
        Role::findByName('manager')->givePermissionTo('view-institution-members');
        Role::findByName('pedagogue')->givePermissionTo('view-institution-members');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('guard_name', 'web')->where('name', 'view-institution-members')->delete();
    }
};
