<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_subscription_id')->constrained()->cascadeOnDelete();
            $table->string('key'); // e.g. "priority_support", "sis_integration"
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->string('billing_cycle')->nullable(); // null = one-time charge
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_addons');
    }
};
