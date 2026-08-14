<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Random passwords: these accounts must go through "forgot password" before first login.
        $superAdmin = User::create([
            'name' => 'Ana Torres',
            'email' => 'superadmin@tereto.app',
            'password' => 'Super-123456',
        ]);
        $superAdmin->assignRole('super_admin');

        $manager = User::create([
            'name' => 'Carlos Ruiz',
            'email' => 'manager@tereto.app',
            'password' => 'Super-123456',
        ]);
        $manager->assignRole('manager');

        $pedagogue = User::create([
            'name' => 'Laura Gómez',
            'email' => 'pedagogue@tereto.app',
            'password' => 'Super-123456',
        ]);
        $pedagogue->assignRole('pedagogue');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::whereIn('email', [
            'superadmin@tereto.app',
            'manager@tereto.app',
            'pedagogue@tereto.app',
        ])->delete();
    }
};
