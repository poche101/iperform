<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Laravel's standard notifications table, created only if it isn't there yet.
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // The supervisor's comment, copied onto the staff member's task log so the Tasks page can show it.
        Schema::table('task_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('task_logs', 'supervisor_note')) {
                $table->text('supervisor_note')->nullable();
            }
            if (!Schema::hasColumn('task_logs', 'supervisor_note_at')) {
                $table->timestamp('supervisor_note_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Intentionally empty: dropping these would delete comments and notifications.
    }
};
