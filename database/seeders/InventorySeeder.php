<?php

namespace Database\Seeders;

use App\Models\BomRecipe;
use App\Models\RawMaterial;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Units
        $gram = Unit::firstOrCreate(['name' => 'Gram', 'symbol' => 'g']);
        $ml = Unit::firstOrCreate(['name' => 'Mililiter', 'symbol' => 'ml']);
        $pcs = Unit::firstOrCreate(['name' => 'Pcs', 'symbol' => 'pcs']);

        // 2. Raw Materials
        $materials = [
            ['name' => 'Bubuk Kopi Robusta', 'unit_id' => $gram->id, 'current_stock' => 5000, 'min_stock' => 500, 'cost_per_unit' => 150], 
            ['name' => 'Susu Kental Manis', 'unit_id' => $ml->id, 'current_stock' => 10000, 'min_stock' => 1000, 'cost_per_unit' => 50],
            ['name' => 'Daun Teh', 'unit_id' => $gram->id, 'current_stock' => 2000, 'min_stock' => 200, 'cost_per_unit' => 100],
            ['name' => 'Gula Pasir', 'unit_id' => $gram->id, 'current_stock' => 5000, 'min_stock' => 500, 'cost_per_unit' => 20],
            ['name' => 'Syrup Leci', 'unit_id' => $ml->id, 'current_stock' => 2000, 'min_stock' => 200, 'cost_per_unit' => 200],
            ['name' => 'Syrup Mangga', 'unit_id' => $ml->id, 'current_stock' => 2000, 'min_stock' => 200, 'cost_per_unit' => 200],
            ['name' => 'Syrup Melon', 'unit_id' => $ml->id, 'current_stock' => 2000, 'min_stock' => 200, 'cost_per_unit' => 200],
            ['name' => 'Syrup Apel', 'unit_id' => $ml->id, 'current_stock' => 2000, 'min_stock' => 200, 'cost_per_unit' => 200],
            ['name' => 'Yakult', 'unit_id' => $pcs->id, 'current_stock' => 50, 'min_stock' => 10, 'cost_per_unit' => 2500],
            ['name' => 'Roti Tawar', 'unit_id' => $pcs->id, 'current_stock' => 100, 'min_stock' => 10, 'cost_per_unit' => 1000],
            ['name' => 'Dimsum Frozen', 'unit_id' => $pcs->id, 'current_stock' => 200, 'min_stock' => 20, 'cost_per_unit' => 3000],
            ['name' => 'Indomie Rasa Soto', 'unit_id' => $pcs->id, 'current_stock' => 100, 'min_stock' => 10, 'cost_per_unit' => 2500],
            ['name' => 'Es Batu', 'unit_id' => $gram->id, 'current_stock' => 20000, 'min_stock' => 1000, 'cost_per_unit' => 5],
            ['name' => 'Telur', 'unit_id' => $pcs->id, 'current_stock' => 100, 'min_stock' => 10, 'cost_per_unit' => 2000],
        ];

        foreach ($materials as $m) {
            RawMaterial::updateOrCreate(['name' => $m['name']], $m);
        }

        $matMap = RawMaterial::pluck('id', 'name');

        // 3. BOM Recipes
        $recipes = [
            'Kopi Saring' => [
                ['id' => $matMap['Bubuk Kopi Robusta'], 'qty' => 15, 'unit' => $gram->id],
            ],
            'Kopi Tubruk' => [
                ['id' => $matMap['Bubuk Kopi Robusta'], 'qty' => 18, 'unit' => $gram->id],
                ['id' => $matMap['Gula Pasir'], 'qty' => 10, 'unit' => $gram->id],
            ],
            'Kopi Susu Panas' => [
                ['id' => $matMap['Bubuk Kopi Robusta'], 'qty' => 15, 'unit' => $gram->id],
                ['id' => $matMap['Susu Kental Manis'], 'qty' => 30, 'unit' => $ml->id],
            ],
            'Kopi Susu Es' => [
                ['id' => $matMap['Bubuk Kopi Robusta'], 'qty' => 15, 'unit' => $gram->id],
                ['id' => $matMap['Susu Kental Manis'], 'qty' => 30, 'unit' => $ml->id],
                ['id' => $matMap['Es Batu'], 'qty' => 150, 'unit' => $gram->id],
            ],
            'Teh Murni' => [
                ['id' => $matMap['Daun Teh'], 'qty' => 5, 'unit' => $gram->id],
                ['id' => $matMap['Gula Pasir'], 'qty' => 15, 'unit' => $gram->id],
            ],
            'Teh Susu' => [
                ['id' => $matMap['Daun Teh'], 'qty' => 5, 'unit' => $gram->id],
                ['id' => $matMap['Susu Kental Manis'], 'qty' => 20, 'unit' => $ml->id],
            ],
            'Teh Leci Es' => [
                ['id' => $matMap['Daun Teh'], 'qty' => 5, 'unit' => $gram->id],
                ['id' => $matMap['Syrup Leci'], 'qty' => 20, 'unit' => $ml->id],
                ['id' => $matMap['Es Batu'], 'qty' => 150, 'unit' => $gram->id],
            ],
            'Mangga Gembira' => [
                ['id' => $matMap['Syrup Mangga'], 'qty' => 30, 'unit' => $ml->id],
                ['id' => $matMap['Es Batu'], 'qty' => 150, 'unit' => $gram->id],
            ],
            'Melon Susu' => [
                ['id' => $matMap['Syrup Melon'], 'qty' => 25, 'unit' => $ml->id],
                ['id' => $matMap['Susu Kental Manis'], 'qty' => 15, 'unit' => $ml->id],
                ['id' => $matMap['Es Batu'], 'qty' => 150, 'unit' => $gram->id],
            ],
            'Apel Warkop' => [
                ['id' => $matMap['Syrup Apel'], 'qty' => 30, 'unit' => $ml->id],
                ['id' => $matMap['Es Batu'], 'qty' => 150, 'unit' => $gram->id],
            ],
            'Leci Yakult' => [
                ['id' => $matMap['Syrup Leci'], 'qty' => 20, 'unit' => $ml->id],
                ['id' => $matMap['Yakult'], 'qty' => 1, 'unit' => $pcs->id],
                ['id' => $matMap['Es Batu'], 'qty' => 100, 'unit' => $gram->id],
            ],
            'Roti Panggang' => [
                ['id' => $matMap['Roti Tawar'], 'qty' => 2, 'unit' => $pcs->id],
                ['id' => $matMap['Susu Kental Manis'], 'qty' => 10, 'unit' => $ml->id],
            ],
            'Dimsum' => [
                ['id' => $matMap['Dimsum Frozen'], 'qty' => 4, 'unit' => $pcs->id],
            ],
            'Indomie' => [
                ['id' => $matMap['Indomie Rasa Soto'], 'qty' => 1, 'unit' => $pcs->id],
            ],
        ];

        foreach ($recipes as $productName => $ingredients) {
            $product = Product::where('name', $productName)->first();
            if ($product) {
                foreach ($ingredients as $ing) {
                    BomRecipe::updateOrCreate(
                        ['product_id' => $product->id, 'raw_material_id' => $ing['id']],
                        ['quantity' => $ing['qty'], 'unit_id' => $ing['unit']]
                    );
                }
            }
        }
    }
}
