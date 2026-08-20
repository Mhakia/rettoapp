<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT users_email_unique');
        DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email) WHERE deleted_at IS NULL');

        DB::statement('ALTER TABLE users DROP CONSTRAINT users_document_type_document_number_unique');
        DB::statement('CREATE UNIQUE INDEX users_document_type_document_number_unique ON users (document_type, document_number) WHERE deleted_at IS NULL');

        DB::statement('ALTER TABLE institutions DROP CONSTRAINT institutions_nit_unique');
        DB::statement('CREATE UNIQUE INDEX institutions_nit_unique ON institutions (nit) WHERE deleted_at IS NULL');

        DB::statement('DROP INDEX institution_memberships_one_active_per_user');
        DB::statement(
            "CREATE UNIQUE INDEX institution_memberships_one_active_per_user
                ON institution_memberships (user_id)
                WHERE status = 'active' AND deleted_at IS NULL"
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX users_email_unique');
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_email_unique UNIQUE (email)');

        DB::statement('DROP INDEX users_document_type_document_number_unique');
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_document_type_document_number_unique UNIQUE (document_type, document_number)');

        DB::statement('DROP INDEX institutions_nit_unique');
        DB::statement('ALTER TABLE institutions ADD CONSTRAINT institutions_nit_unique UNIQUE (nit)');

        DB::statement('DROP INDEX institution_memberships_one_active_per_user');
        DB::statement(
            "CREATE UNIQUE INDEX institution_memberships_one_active_per_user
                ON institution_memberships (user_id)
                WHERE status = 'active'"
        );
    }
};
