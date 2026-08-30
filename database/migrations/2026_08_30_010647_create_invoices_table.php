<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_subscription_id')->constrained()->cascadeOnDelete();

            $table->string('number')->unique(); // e.g. "INV-2026-000123"
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('billed_student_count');

            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('currency', 3)->default('COP');

            // How this invoice gets paid: 'stripe' routes through Cashier;
            // 'manual' covers convenios and any other out-of-band payment
            // (transferencia, orden de compra, etc.).
            $table->string('payment_method')->default('manual');
            $table->string('stripe_invoice_id')->nullable()->unique();
            $table->string('status')->default('pending'); // pending | paid | overdue | cancelled
            $table->date('due_at');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
