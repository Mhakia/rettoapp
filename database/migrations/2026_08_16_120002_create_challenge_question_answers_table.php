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
        Schema::create('challenge_question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_completion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_question_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'submitted', 'verified', 'rejected'])->default('pending');
            $table->string('evidence_path')->nullable();
            $table->unsignedInteger('points_earned')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['challenge_completion_id', 'challenge_question_id'], 'challenge_question_answers_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_question_answers');
    }
};
