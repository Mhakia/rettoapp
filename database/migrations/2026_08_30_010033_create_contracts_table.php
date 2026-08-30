<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('type')->default('standard'); // standard | corporate | departmental_agreement | national_agreement
            $table->string('entity_name')->nullable(); // e.g. "Secretaría de Educación de Atlántico"
            $table->decimal('default_price_per_student', 12, 2)->nullable();
            $table->unsignedInteger('default_included_students')->nullable();
            $table->string('negotiated_by')->nullable();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->string('status')->default('active'); // active | expired | cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
