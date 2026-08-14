<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('institution_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['active', 'withdrawn', 'graduated', 'transferred'])->default('active');
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        // A user can only have one active membership at a time (portable identity).
        DB::statement(
            'CREATE UNIQUE INDEX institution_memberships_one_active_per_user
                ON institution_memberships (user_id)
                WHERE status = \'active\''
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_memberships');
    }
};
