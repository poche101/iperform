<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Each column is checked first so this is safe even if the create migration already has them
        if (!Schema::hasColumn('task_logs', 'log_type')) {
            Schema::table('task_logs', function (Blueprint $table) {
                // Existing rows become 'daily' via the default
                $table->string('log_type', 10)->default('daily')->index();
            });
        }

        if (!Schema::hasColumn('task_logs', 'week_start')) {
            Schema::table('task_logs', function (Blueprint $table) {
                $table->date('week_start')->nullable();
            });
        }

        if (!Schema::hasColumn('task_logs', 'week_end')) {
            Schema::table('task_logs', function (Blueprint $table) {
                $table->date('week_end')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('task_logs', 'log_type')) {
            Schema::table('task_logs', function (Blueprint $table) {
                $table->dropIndex(['log_type']);
            });
        }

        Schema::table('task_logs', function (Blueprint $table) {
            $drop = array_filter(
                ['log_type', 'week_start', 'week_end'],
                fn ($col) => Schema::hasColumn('task_logs', $col)
            );

            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
