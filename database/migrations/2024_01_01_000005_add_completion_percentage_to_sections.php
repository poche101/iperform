<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appraisal_kras', function (Blueprint $table) {
            $table->unsignedTinyInteger('completion_percentage')->default(0)->after('achievement')
                ->comment('0-100% — how much of this KRA/target was completed');
        });

        Schema::table('appraisal_tasks', function (Blueprint $table) {
            $table->unsignedTinyInteger('completion_percentage')->default(0)->after('performance')
                ->comment('0-100% — how much of this task was completed');
        });

        Schema::table('appraisal_innovations', function (Blueprint $table) {
            $table->unsignedTinyInteger('completion_percentage')->default(0)->after('impact')
                ->comment('0-100% — how far along this initiative is');
        });
    }

    public function down(): void
    {
        Schema::table('appraisal_kras', fn(Blueprint $t) => $t->dropColumn('completion_percentage'));
        Schema::table('appraisal_tasks', fn(Blueprint $t) => $t->dropColumn('completion_percentage'));
        Schema::table('appraisal_innovations', fn(Blueprint $t) => $t->dropColumn('completion_percentage'));
    }
};
