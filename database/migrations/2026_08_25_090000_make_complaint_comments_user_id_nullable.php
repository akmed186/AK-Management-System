<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A comment's author account can now be deleted (see UserController::destroy)
     * without losing the comment itself — matches how every other user
     * reference in the app (activities.causer_id, expenses.recorded_by_user_id,
     * maintenance_requests.assigned_to_user_id) already nulls out instead of
     * cascading.
     */
    public function up(): void
    {
        Schema::table('complaint_comments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('complaint_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('complaint_comments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('complaint_comments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('complaint_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        Schema::table('complaint_comments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
