<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();

            // Informational only: where these values originally came from.
            // Editing the fields below never requires touching the plan or contract.
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained()->nullOnDelete();

            // Resolved, always-explicit terms for this institution. Copied from the
            // plan/contract at creation time, then freely editable by the commercial team.
            $table->decimal('base_price', 12, 2);
            $table->unsignedInteger('included_students');
            $table->decimal('price_per_extra_student', 12, 2);

            $table->string('discount_type')->nullable(); // percentage | fixed
            $table->decimal('discount_value', 12, 2)->nullable();

            $table->string('billing_cycle')->default('monthly'); // monthly | quarterly | annual
            $table->json('features')->nullable(); // overrides the plan's features for this institution

            $table->string('status')->default('active'); // active | paused | cancelled | ended
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_subscriptions');
    }
};
