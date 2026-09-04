<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * An institution can only have one active subscription at a time.
     * This unique index enforces it at the database level.
     */
    public function up(): void
    {
        DB::statement(
            "CREATE UNIQUE INDEX institution_subscriptions_one_active_per_institution
                ON institution_subscriptions (institution_id)
                WHERE status = 'active' AND deleted_at IS NULL"
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS institution_subscriptions_one_active_per_institution');
    }
};
