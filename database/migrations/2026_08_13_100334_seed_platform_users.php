<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // TEAM_PASSWORD in .env; falls back to a random password (forces "forgot password") if unset.
        $password = config('services.team_password') ?: Str::random(32);

        $superAdmin = User::create([
            'name' => 'Ana Torres',
            'email' => 'superadmin@tereto.app',
            'password' => $password,
        ]);
        $superAdmin->assignRole('super_admin');

        $manager = User::create([
            'name' => 'Carlos Ruiz',
            'email' => 'manager@tereto.app',
            'password' => $password,
        ]);
        $manager->assignRole('manager');

        $pedagogue = User::create([
            'name' => 'Laura Gómez',
            'email' => 'pedagogue@tereto.app',
            'password' => $password,
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
