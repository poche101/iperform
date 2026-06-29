<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('task_logs', function (Blueprint $table) {
            $table->foreignId('appraisal_id')
                  ->nullable()
                  ->constrained('appraisals')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('task_logs', function (Blueprint $table) {
            $table->dropForeign(['appraisal_id']);
            $table->dropColumn('appraisal_id');
        });
    }
};
