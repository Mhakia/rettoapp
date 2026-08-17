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
        // table scan, so name/document/nit search stays fast no matter how large the tables get.
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement('CREATE INDEX institutions_name_trgm_idx ON institutions USING gin (name gin_trgm_ops)');
        DB::statement('CREATE INDEX institutions_nit_trgm_idx ON institutions USING gin (nit gin_trgm_ops)');
        DB::statement('CREATE INDEX users_name_trgm_idx ON users USING gin (name gin_trgm_ops)');
        DB::statement('CREATE INDEX users_document_number_trgm_idx ON users USING gin (document_number gin_trgm_ops)');
        DB::statement('CREATE INDEX students_document_number_trgm_idx ON students USING gin (document_number gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS institutions_name_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS institutions_nit_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS users_name_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS users_document_number_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS students_document_number_trgm_idx');
    }
};
