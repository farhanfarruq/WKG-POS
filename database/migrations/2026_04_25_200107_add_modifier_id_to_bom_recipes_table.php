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
        Schema::table('bom_recipes', function (Blueprint $table) {
            // Drop foreign keys if they exist (ignoring errors if not exists is hard in Schema, so we assume they exist if the unique index exists)
            try {
                $table->dropForeign(['product_id']);
                $table->dropForeign(['raw_material_id']);
                $table->dropUnique(['product_id', 'raw_material_id']);
            } catch (\Exception $e) {
                // Already dropped or different name
            }
            
            // Modify columns
            $table->foreignId('product_id')->nullable()->change();
            
            if (!Schema::hasColumn('bom_recipes', 'modifier_id')) {
                $table->foreignId('modifier_id')->nullable()->after('product_id')->constrained()->cascadeOnDelete();
            }

            // Re-add foreign keys and new unique index
            try {
                $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
                $table->foreign('raw_material_id')->references('id')->on('raw_materials')->cascadeOnDelete();
                $table->unique(['product_id', 'modifier_id', 'raw_material_id'], 'bom_recipes_unique');
            } catch (\Exception $e) {
                // Already exists
            }
        });
    }

    public function down(): void
    {
        Schema::table('bom_recipes', function (Blueprint $table) {
            try {
                $table->dropUnique('bom_recipes_unique');
                $table->dropForeign(['modifier_id']);
                $table->dropColumn('modifier_id');
            } catch (\Exception $e) {}

            try {
                $table->dropForeign(['product_id']);
                $table->foreignId('product_id')->nullable(false)->change();
                $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
                $table->unique(['product_id', 'raw_material_id']);
            } catch (\Exception $e) {}
        });
    }
};
