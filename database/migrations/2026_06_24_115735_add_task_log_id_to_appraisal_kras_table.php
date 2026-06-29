<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appraisal_kras', function (Blueprint $table) {
            $table->foreignId('task_log_id')
                  ->nullable()
                  ->constrained('task_logs')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('appraisal_kras', function (Blueprint $table) {
            $table->dropForeign(['task_log_id']);
            $table->dropColumn('task_log_id');
        });
    }
};
