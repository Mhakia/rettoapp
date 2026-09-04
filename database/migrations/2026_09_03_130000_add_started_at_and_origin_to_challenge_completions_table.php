<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('challenge_completions', function (Blueprint $table) {
            // When the student first opened this challenge (used to measure how long it took to answer).
            $table->timestamp('started_at')->nullable()->after('challenge_id');
            // Where the student was when they submitted: 'class_session' (in school) or 'guardian' (at home).
            $table->string('origin')->nullable()->after('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_completions', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'origin']);
        });
    }
};
