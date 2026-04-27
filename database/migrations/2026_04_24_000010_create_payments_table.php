<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method'); // PaymentMethod enum
            $table->decimal('amount', 12, 2);
            $table->string('reference_number')->nullable(); // for QRIS/card
            $table->string('status')->default('success'); // success|failed|pending
            $table->json('meta')->nullable(); // payment gateway response
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
