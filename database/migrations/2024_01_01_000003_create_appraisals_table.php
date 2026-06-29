<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('appraisal_cycles')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['drafting', 'submitted', 'with_staff_performance', 'approved'])->default('drafting');

            // Section 5: Performance Challenges (stored as JSON)
            $table->json('section5')->nullable();
            // Section 6: Training Needs (stored as JSON)
            $table->json('section6')->nullable();

            // Section 7: Compliance scores (stored as JSON)
            $table->json('section7_items')->nullable();
            $table->text('overall_contribution')->nullable();
            $table->text('key_strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();

            // Supervisor work confirmation
            $table->integer('salary_percent')->nullable();
            $table->text('supervisor_comments')->nullable();
            $table->boolean('supervisor_confirmed')->default(false);

            // Staff Performance section
            $table->decimal('staff_performance_s1_weighted', 5, 2)->nullable();
            $table->decimal('staff_performance_s2_weighted', 5, 2)->nullable();
            $table->decimal('staff_performance_s3_weighted', 5, 2)->nullable();
            $table->decimal('staff_performance_s4_weighted', 5, 2)->nullable();
            $table->decimal('staff_performance_overall', 5, 2)->nullable();
            $table->string('staff_performance_grade')->nullable();
            $table->text('staff_performance_comments')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('forwarded_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisals');
    }
};
