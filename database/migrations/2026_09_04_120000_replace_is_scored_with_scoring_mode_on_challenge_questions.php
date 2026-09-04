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
        Schema::table('challenge_questions', function (Blueprint $table) {
            // automatic = correctness checked against is_correct options; manual = no objectively
            // correct answer, a teacher decides on review whether to award the points; none = never
            // awards points (purely reflective).
            $table->enum('scoring_mode', ['automatic', 'manual', 'none'])->default('automatic')->after('is_scored');
        });

        DB::table('challenge_questions')->where('is_scored', true)->update(['scoring_mode' => 'automatic']);
        DB::table('challenge_questions')->where('is_scored', false)->update(['scoring_mode' => 'none']);

        Schema::table('challenge_questions', function (Blueprint $table) {
            $table->dropColumn('is_scored');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_questions', function (Blueprint $table) {
            $table->boolean('is_scored')->default(true)->after('min_selections');
        });

        DB::table('challenge_questions')->where('scoring_mode', 'none')->update(['is_scored' => false]);
        DB::table('challenge_questions')->where('scoring_mode', '!=', 'none')->update(['is_scored' => true]);

        Schema::table('challenge_questions', function (Blueprint $table) {
            $table->dropColumn('scoring_mode');
        });
    }
};
