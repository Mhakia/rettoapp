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
        Schema::create('challenge_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('points')->default(0);
            $table->enum('answer_type', ['choice', 'evidence'])->default('choice');
            // Only used when answer_type = choice: single = exactly 1 of N, multiple = up to 3 of N.
            $table->enum('answer_mode', ['single', 'multiple'])->nullable();
            // Only used when answer_mode = multiple: minimum correct options the user must select (1-3).
            $table->unsignedTinyInteger('min_selections')->nullable();
            // Whether this question has a correct answer / awards points, or is purely reflective.
            $table->boolean('is_scored')->default(true);
            // Only relevant for choice questions: auto-verify on submit vs. require manual teacher review.
            $table->boolean('auto_verify')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_questions');
    }
};
