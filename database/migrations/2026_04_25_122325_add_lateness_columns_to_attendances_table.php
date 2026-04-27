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
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('work_shift_id')->nullable()->after('shift_id')->constrained('work_shifts')->nullOnDelete();
            $table->boolean('is_late')->default(false)->after('clock_in_method');
            $table->integer('late_minutes')->nullable()->after('is_late');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['work_shift_id']);
            $table->dropColumn(['work_shift_id', 'is_late', 'late_minutes']);
        });
    }
};
