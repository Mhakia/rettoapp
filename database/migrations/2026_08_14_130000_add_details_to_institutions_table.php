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
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('contact_first_name')->nullable()->after('phone');
            $table->string('contact_middle_name')->nullable()->after('contact_first_name');
            $table->string('contact_last_name')->nullable()->after('contact_middle_name');
            $table->string('contact_second_last_name')->nullable()->after('contact_last_name');
            $table->string('contact_document_type')->nullable()->after('contact_second_last_name');
            $table->string('contact_document_number')->nullable()->after('contact_document_type');
            $table->string('contact_email')->nullable()->after('contact_document_number');
            $table->string('contact_phone')->nullable()->after('contact_email');

            $table->string('principal_name')->nullable()->after('contact_phone');
            $table->string('principal_document_type')->nullable()->after('principal_name');
            $table->string('principal_document_number')->nullable()->after('principal_document_type');
            $table->date('principal_started_at')->nullable()->after('principal_document_number');

            $table->string('country')->nullable()->after('principal_started_at');
            $table->string('state')->nullable()->after('country');
            $table->string('city')->nullable()->after('state');
            $table->enum('entity_type', ['public', 'private'])->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn([
                'contact_first_name',
                'contact_middle_name',
                'contact_last_name',
                'contact_second_last_name',
                'contact_document_type',
                'contact_document_number',
                'contact_email',
                'contact_phone',
                'principal_name',
                'principal_document_type',
                'principal_document_number',
                'principal_started_at',
                'country',
                'state',
                'city',
                'entity_type',
            ]);
        });
    }
};
