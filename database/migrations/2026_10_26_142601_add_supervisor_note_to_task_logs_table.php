<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
        Schema::table('task_logs', function (Blueprint $table) {
            if (Schema::hasColumn('task_logs', 'supervisor_note')) {
                $table->dropColumn('supervisor_note');
            }
            if (Schema::hasColumn('task_logs', 'supervisor_note_at')) {
                $table->dropColumn('supervisor_note_at');
            }
        });
    }
};
