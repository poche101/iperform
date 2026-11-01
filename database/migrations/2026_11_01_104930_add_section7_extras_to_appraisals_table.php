<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columns the Section 7 forms (supervisor + HR) already read and write.
 * Each is added only if missing, so this is safe to run on a database that already has some of them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appraisals', function (Blueprint $table) {
            if (!Schema::hasColumn('appraisals', 'section7_misconduct')) {
                $table->json('section7_misconduct')->nullable();
            }
            foreach (['overall_contribution_score', 'key_strengths_score', 'areas_for_improvement_score'] as $col) {
                if (!Schema::hasColumn('appraisals', $col)) {
                    $table->unsignedTinyInteger($col)->nullable();
                }
            }
            if (!Schema::hasColumn('appraisals', 'total_score')) {
                $table->decimal('total_score', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('appraisals', 'total_score_comment')) {
                $table->text('total_score_comment')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('appraisals', function (Blueprint $table) {
            foreach (['section7_misconduct', 'overall_contribution_score', 'key_strengths_score', 'areas_for_improvement_score', 'total_score', 'total_score_comment'] as $col) {
                if (Schema::hasColumn('appraisals', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
