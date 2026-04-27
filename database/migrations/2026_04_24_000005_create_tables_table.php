<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Meja 1", "Lantai 2 - A"
            $table->string('area')->nullable(); // e.g., "indoor", "outdoor", "vip"
            $table->integer('capacity')->default(4);
            $table->string('status')->default('available'); // available, occupied, reserved
            $table->integer('pos_x')->default(0); // visual layout x-coord
            $table->integer('pos_y')->default(0); // visual layout y-coord
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
