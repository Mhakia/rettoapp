<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_subscriptions', function (Blueprint $table) {
            // Wompi payment source id (tokenized card/Nequi/etc.), used for automatic
            // recurring charges (Credential On File) instead of a Payment Link each cycle.
            $table->string('wompi_payment_source_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('institution_subscriptions', function (Blueprint $table) {
            $table->dropColumn('wompi_payment_source_id');
        });
    }
};
