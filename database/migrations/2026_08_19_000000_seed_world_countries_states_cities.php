<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nombres de las tablas que crea/usa nnjeim/world.
     * Ajusta si publicaste el config con otros nombres.
     */
    private array $worldTables = [
        'countries',
        'states',
        'cities',
    ];

    public function up(): void
    {
        // 1. Si las tablas ni siquiera existen todavía, no hacemos nada
        //    (las migraciones propias del paquete deben correr antes que esta).
        foreach ($this->worldTables as $table) {
            if (! Schema::hasTable($table)) {
                Log::warning("WorldSeeder: la tabla '{$table}' no existe todavía. Se omite el seed.");

                return;
            }
        }

        // 2. Si ya hay datos, no volvemos a sembrar (idempotencia).
        $alreadySeeded = DB::table('countries')->exists();

        if ($alreadySeeded) {
            Log::info('WorldSeeder: los datos ya existen, se omite el seeding.');

            return;
        }

        // 3. Sembramos.
        Log::info('WorldSeeder: sembrando países, estados y ciudades...');

        Artisan::call('db:seed', [
            '--class' => 'WorldSeeder',
            '--force' => true, // necesario en producción
        ]);

        Log::info('WorldSeeder: seeding completado.');
    }

    public function down(): void
    {
        // Deliberadamente vacío: no queremos borrar el catálogo de
        // países/estados/ciudades al hacer rollback.
        // Si de verdad necesitas revertirlo, descomenta:
        //
        // DB::table('cities')->truncate();
        // DB::table('states')->truncate();
        // DB::table('countries')->truncate();
    }
};
