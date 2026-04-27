<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('product_name'); // snapshot
            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity');
            $table->decimal('modifier_price', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->string('notes')->nullable();
            $table->string('kds_status')->default('pending'); // KDSStatus enum
            $table->timestamp('kds_sent_at')->nullable();
            $table->timestamp('kds_completed_at')->nullable();
            $table->json('modifiers')->nullable(); // snapshot of selected modifiers
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
