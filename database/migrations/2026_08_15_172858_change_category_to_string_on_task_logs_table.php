<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite workaround: drop and re-add or handle via schema builder if needed,
            // or skip if it's already a text/varchar type in SQLite.
            // SQLite treats types loosely, so changing types dynamically is often unnecessary.
            return;
        }

        Schema::table('task_logs', function (Blueprint $table) {
            $table->string('category')->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('task_logs', function (Blueprint $table) {
            // revert logic if necessary
        });
    }
};
