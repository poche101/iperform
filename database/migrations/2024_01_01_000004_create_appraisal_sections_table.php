<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Section 1: KRA
        Schema::create('appraisal_kras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_id')->constrained()->cascadeOnDelete();
            $table->integer('sn');
            $table->text('kra');
            $table->text('target');
            $table->text('achievement')->nullable();
            $table->integer('staff_score')->nullable();
            $table->integer('supervisor_score')->nullable();
            $table->timestamps();
        });

        // Section 2: Routine Tasks
        Schema::create('appraisal_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_id')->constrained()->cascadeOnDelete();
            $table->integer('sn');
            $table->text('task');
            $table->text('performance')->nullable();
            $table->integer('staff_score')->nullable();
            $table->integer('supervisor_score')->nullable();
            $table->timestamps();
        });

        // Section 3: Innovations
        Schema::create('appraisal_innovations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_id')->constrained()->cascadeOnDelete();
            $table->integer('sn');
            $table->text('idea');
            $table->text('impact')->nullable();
            $table->integer('staff_score')->nullable();
            $table->integer('supervisor_score')->nullable();
            $table->timestamps();
        });

        // Section 4: Core Competencies
        Schema::create('appraisal_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_id')->constrained()->cascadeOnDelete();
            $table->integer('sn');
            $table->string('competency');
            $table->integer('staff_score')->nullable();
            $table->integer('supervisor_score')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisal_competencies');
        Schema::dropIfExists('appraisal_innovations');
        Schema::dropIfExists('appraisal_tasks');
        Schema::dropIfExists('appraisal_kras');
    }
};
