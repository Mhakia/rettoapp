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
        Schema::table('users', function (Blueprint $table) {
            // Fixed institution scope, only used by institution_admin.
            $table->foreignId('institution_id')->nullable()->after('id')->constrained()->nullOnDelete();
            // Only populated for teacher and guardian (student's document lives on the students table).
            $table->string('document_type')->nullable()->after('email');
            $table->string('document_number')->nullable()->after('document_type');

            $table->unique(['document_type', 'document_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['document_type', 'document_number']);
            $table->dropConstrainedForeignId('institution_id');
            $table->dropColumn(['document_type', 'document_number']);
        });
    }
};
