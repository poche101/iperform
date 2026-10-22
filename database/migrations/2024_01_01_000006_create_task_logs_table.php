<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cycle_id')->constrained('appraisal_cycles')->cascadeOnDelete();

            // Daily or weekly log entry
            $table->string('log_type', 10)->default('daily')->index();

            $table->string('title');
            $table->date('date');                    // daily: the day worked; weekly: Monday of the week
            $table->date('week_start')->nullable();  // weekly only (Monday)
            $table->date('week_end')->nullable();    // weekly only (Sunday)
            $table->text('details')->nullable();
            $table->enum('category', ['KRA', 'Routine', 'Innovation'])->default('Routine');
            $table->unsignedTinyInteger('self_score')->nullable(); // 0-10
            $table->unsignedTinyInteger('completion_percentage')->default(0); // 0-100
            $table->enum('status', ['pending', 'awaiting', 'graded'])->default('pending');
            $table->foreignId('task_log_id')->nullable()->constrained('task_logs')->onDelete('set null');
            // Supervisor feedback
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('supervisor_score')->nullable(); // 0-10
            $table->text('supervisor_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_logs');
    }
};
