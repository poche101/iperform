<?php

use App\Models\AppraisalInnovation;
use App\Models\AppraisalKra;
use App\Models\AppraisalTask;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([AppraisalKra::class, AppraisalTask::class, AppraisalInnovation::class] as $model) {
            $table = (new $model)->getTable();

            if (!Schema::hasColumn($table, 'supervisor_comment')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->text('supervisor_comment')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        // Intentionally empty: dropping the column would delete supervisors' comments.
    }
};
