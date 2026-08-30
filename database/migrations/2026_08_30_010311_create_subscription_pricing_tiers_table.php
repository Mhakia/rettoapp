<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_subscription_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('min_students');
            $table->unsignedInteger('max_students')->nullable(); // null = unbounded (last tier)
            $table->decimal('price_per_student', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_pricing_tiers');
    }
};
