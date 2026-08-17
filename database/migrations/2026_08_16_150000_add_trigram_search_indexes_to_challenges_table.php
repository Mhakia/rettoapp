<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Trigram GIN indexes let '%term%' ILIKE searches use an index instead of a full
        // table scan, so title/category search stays fast no matter how large the table gets.
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement('CREATE INDEX challenges_title_trgm_idx ON challenges USING gin (title gin_trgm_ops)');
        DB::statement('CREATE INDEX challenges_category_trgm_idx ON challenges USING gin (category gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS challenges_title_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS challenges_category_trgm_idx');
    }
};
