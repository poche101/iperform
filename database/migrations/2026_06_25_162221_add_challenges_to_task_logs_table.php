<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('task_logs', function (Blueprint $table) {
        $table->text('challenge_identified')->nullable()->after('details');
        $table->text('challenge_impact')->nullable()->after('challenge_identified');
    });
}

public function down(): void
{
    Schema::table('task_logs', function (Blueprint $table) {
        $table->dropColumn(['challenge_identified', 'challenge_impact']);
    });
}
};
