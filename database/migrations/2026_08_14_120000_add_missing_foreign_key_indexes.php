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
            $table->index('created_by');
            $table->index('institution_membership_id');
            $table->index('student_id');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->index('institution_id');
        });

        Schema::table('challenge_completions', function (Blueprint $table) {
            $table->index('challenge_id');
            $table->index('institution_membership_id');
            $table->index('user_id');
            $table->index('verified_by');
        });

        Schema::table('challenge_institutions', function (Blueprint $table) {
            $table->index('institution_id');
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->index('created_by');
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->index('author_id');
            $table->index('institution_membership_id');
            $table->index('student_id');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->index('branch_id');
            $table->index('institution_id');
        });

        Schema::table('guardian_student', function (Blueprint $table) {
            $table->index('student_id');
        });

        Schema::table('individual_support_plans', function (Blueprint $table) {
            $table->index('created_by');
            $table->index('institution_membership_id');
            $table->index('student_id');
        });

        Schema::table('institution_memberships', function (Blueprint $table) {
            $table->index('group_id');
            $table->index('institution_id');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->index('institution_id');
            $table->index('recipient_id');
        });

        Schema::table(config('permission.table_names')['role_has_permissions'], function (Blueprint $table) {
            $table->index('role_id');
        });

        Schema::table('support_plan_versions', function (Blueprint $table) {
            $table->index('created_by');
            $table->index('individual_support_plan_id');
        });

        Schema::table('teacher_group', function (Blueprint $table) {
            $table->index('group_id');
            $table->index('institution_membership_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('institution_id');
        });

        Schema::table('wellbeing_indicators', function (Blueprint $table) {
            $table->index('institution_membership_id');
            $table->index('recorded_by');
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['institution_membership_id']);
            $table->dropIndex(['student_id']);
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropIndex(['institution_id']);
        });

        Schema::table('challenge_completions', function (Blueprint $table) {
            $table->dropIndex(['challenge_id']);
            $table->dropIndex(['institution_membership_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['verified_by']);
        });

        Schema::table('challenge_institutions', function (Blueprint $table) {
            $table->dropIndex(['institution_id']);
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropIndex(['author_id']);
            $table->dropIndex(['institution_membership_id']);
            $table->dropIndex(['student_id']);
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['branch_id']);
            $table->dropIndex(['institution_id']);
        });

        Schema::table('guardian_student', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
        });

        Schema::table('individual_support_plans', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['institution_membership_id']);
            $table->dropIndex(['student_id']);
        });

        Schema::table('institution_memberships', function (Blueprint $table) {
            $table->dropIndex(['group_id']);
            $table->dropIndex(['institution_id']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['institution_id']);
            $table->dropIndex(['recipient_id']);
        });

        Schema::table(config('permission.table_names')['role_has_permissions'], function (Blueprint $table) {
            $table->dropIndex(['role_id']);
        });

        Schema::table('support_plan_versions', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['individual_support_plan_id']);
        });

        Schema::table('teacher_group', function (Blueprint $table) {
            $table->dropIndex(['group_id']);
            $table->dropIndex(['institution_membership_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['institution_id']);
        });

        Schema::table('wellbeing_indicators', function (Blueprint $table) {
            $table->dropIndex(['institution_membership_id']);
            $table->dropIndex(['recorded_by']);
            $table->dropIndex(['student_id']);
        });
    }
};
