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
        Schema::table('alerts', function (Blueprint $table) {
            $table->index('deleted_by');
        });

        Schema::table('challenge_questions', function (Blueprint $table) {
            $table->index('challenge_id');
        });

        Schema::table('challenge_question_options', function (Blueprint $table) {
            $table->index('challenge_question_id');
        });

        Schema::table('challenge_question_answers', function (Blueprint $table) {
            $table->index('challenge_question_id');
            $table->index('verified_by');
        });

        Schema::table('challenge_question_answer_selections', function (Blueprint $table) {
            $table->index('challenge_question_option_id');
        });

        Schema::table('challenge_views', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->index('deleted_by');
        });

        Schema::table('class_sessions', function (Blueprint $table) {
            $table->index('challenge_id');
            $table->index('created_by');
            $table->index('group_id');
        });

        Schema::table('import_batches', function (Blueprint $table) {
            $table->index('institution_id');
            $table->index('uploaded_by');
        });

        Schema::table('individual_support_plans', function (Blueprint $table) {
            $table->index('deleted_by');
        });

        Schema::table('institution_memberships', function (Blueprint $table) {
            $table->index('deleted_by');
        });

        Schema::table('institution_subscriptions', function (Blueprint $table) {
            $table->index('contract_id');
            $table->index('plan_id');
        });

        Schema::table('institutions', function (Blueprint $table) {
            $table->index('deleted_by');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->index('invoice_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('institution_id');
            $table->index('institution_subscription_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->index('import_batch_id');
        });

        Schema::table('subscription_addons', function (Blueprint $table) {
            $table->index('institution_subscription_id');
        });

        Schema::table('subscription_pricing_tiers', function (Blueprint $table) {
            $table->index('institution_subscription_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('deleted_by');
            $table->index('import_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropIndex(['deleted_by']);
        });

        Schema::table('challenge_questions', function (Blueprint $table) {
            $table->dropIndex(['challenge_id']);
        });

        Schema::table('challenge_question_options', function (Blueprint $table) {
            $table->dropIndex(['challenge_question_id']);
        });

        Schema::table('challenge_question_answers', function (Blueprint $table) {
            $table->dropIndex(['challenge_question_id']);
            $table->dropIndex(['verified_by']);
        });

        Schema::table('challenge_question_answer_selections', function (Blueprint $table) {
            $table->dropIndex(['challenge_question_option_id']);
        });

        Schema::table('challenge_views', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->dropIndex(['deleted_by']);
        });

        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropIndex(['challenge_id']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['group_id']);
        });

        Schema::table('import_batches', function (Blueprint $table) {
            $table->dropIndex(['institution_id']);
            $table->dropIndex(['uploaded_by']);
        });

        Schema::table('individual_support_plans', function (Blueprint $table) {
            $table->dropIndex(['deleted_by']);
        });

        Schema::table('institution_memberships', function (Blueprint $table) {
            $table->dropIndex(['deleted_by']);
        });

        Schema::table('institution_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['contract_id']);
            $table->dropIndex(['plan_id']);
        });

        Schema::table('institutions', function (Blueprint $table) {
            $table->dropIndex(['deleted_by']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex(['invoice_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['institution_id']);
            $table->dropIndex(['institution_subscription_id']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['import_batch_id']);
        });

        Schema::table('subscription_addons', function (Blueprint $table) {
            $table->dropIndex(['institution_subscription_id']);
        });

        Schema::table('subscription_pricing_tiers', function (Blueprint $table) {
            $table->dropIndex(['institution_subscription_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['deleted_by']);
            $table->dropIndex(['import_batch_id']);
        });
    }
};
