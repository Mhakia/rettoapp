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
        Schema::create('challenge_question_answer_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_question_answer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_question_option_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['challenge_question_answer_id', 'challenge_question_option_id'], 'challenge_question_answer_selections_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_question_answer_selections');
    }
};
