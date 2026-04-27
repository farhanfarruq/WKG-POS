<?php

namespace Database\Seeders;

use App\Models\BomRecipe;
use App\Models\Category;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Table;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Units
        $gram   = Unit::firstOrCreate(['name' => 'Gram'],     ['symbol' => 'g']);
        $ml     = Unit::firstOrCreate(['name' => 'Mililiter'], ['symbol' => 'ml']);
        $pcs    = Unit::firstOrCreate(['name' => 'Pcs'],       ['symbol' => 'pcs']);
        $sachet = Unit::firstOrCreate(['name' => 'Sachet'],    ['symbol' => 'sachet']);

        // Categories
        $catKopi    = Category::updateOrCreate(['slug' => 'kopi'],    ['name' => 'Kopi',       'sort_order' => 1]);
        $catNonKopi = Category::updateOrCreate(['slug' => 'non-kopi'], ['name' => 'Non-Kopi',   'sort_order' => 2]);
        $catMakanan = Category::updateOrCreate(['slug' => 'makanan'],  ['name' => 'Makanan',    'sort_order' => 3]);

        // Raw Materials
        $kopi   = RawMaterial::updateOrCreate(['name' => 'Biji Kopi'], ['unit_id' => $gram->id, 'current_stock' => 5000, 'min_stock' => 500, 'cost_per_unit' => 0.15]);
        $susu   = RawMaterial::updateOrCreate(['name' => 'Susu Segar'], ['unit_id' => $ml->id,   'current_stock' => 10000, 'min_stock' => 1000, 'cost_per_unit' => 0.02]);
        $gula   = RawMaterial::updateOrCreate(['name' => 'Gula Pasir'], ['unit_id' => $gram->id, 'current_stock' => 5000, 'min_stock' => 500, 'cost_per_unit' => 0.01]);
        $teh    = RawMaterial::updateOrCreate(['name' => 'Teh Kering'], ['unit_id' => $gram->id, 'current_stock' => 2000, 'min_stock' => 200, 'cost_per_unit' => 0.05]);
        $mie    = RawMaterial::updateOrCreate(['name' => 'Indomie'],    ['unit_id' => $pcs->id,  'current_stock' => 100, 'min_stock' => 10, 'cost_per_unit' => 3500]);
        $telur  = RawMaterial::updateOrCreate(['name' => 'Telur'],      ['unit_id' => $pcs->id,  'current_stock' => 100, 'min_stock' => 10, 'cost_per_unit' => 2000]);

        // Modifier Groups
        $sugarLevel = ModifierGroup::updateOrCreate(['name' => 'Level Gula'], ['is_required' => false, 'is_multiple' => false]);
        $rotiFlavor = ModifierGroup::updateOrCreate(['name' => 'Rasa Roti'], ['is_required' => true, 'is_multiple' => false]);
        $indomieTopping = ModifierGroup::updateOrCreate(['name' => 'Topping Indomie'], ['is_required' => false, 'is_multiple' => true]);

        Modifier::updateOrCreate(['modifier_group_id' => $sugarLevel->id, 'name' => 'Normal'], ['additional_price' => 0, 'sort_order' => 1]);
        Modifier::updateOrCreate(['modifier_group_id' => $sugarLevel->id, 'name' => 'Less Sugar'], ['additional_price' => 0, 'sort_order' => 2]);
        Modifier::updateOrCreate(['modifier_group_id' => $sugarLevel->id, 'name' => 'No Sugar'], ['additional_price' => 0, 'sort_order' => 3]);

        Modifier::updateOrCreate(['modifier_group_id' => $rotiFlavor->id, 'name' => 'Coklat'], ['additional_price' => 0, 'sort_order' => 1]);
        Modifier::updateOrCreate(['modifier_group_id' => $rotiFlavor->id, 'name' => 'Tiramisyu'], ['additional_price' => 0, 'sort_order' => 2]);
        Modifier::updateOrCreate(['modifier_group_id' => $rotiFlavor->id, 'name' => 'Srikaya'], ['additional_price' => 0, 'sort_order' => 3]);

        Modifier::updateOrCreate(['modifier_group_id' => $indomieTopping->id, 'name' => 'Tambah Telur'], ['additional_price' => 3000, 'sort_order' => 1]);

        // Products Data
        $products = [
            // KOPI
            ['cat' => $catKopi, 'name' => 'Kopi Saring', 'price' => 9000],
            ['cat' => $catKopi, 'name' => 'Kopi Tubruk', 'price' => 9000],
            ['cat' => $catKopi, 'name' => 'Kopi Susu Panas', 'price' => 10000],
            ['cat' => $catKopi, 'name' => 'Kopi Susu Es', 'price' => 13000],
            
            // NON KOPI / TEH
            ['cat' => $catNonKopi, 'name' => 'Teh Murni', 'price' => 5000],
            ['cat' => $catNonKopi, 'name' => 'Teh Susu', 'price' => 10000],
            ['cat' => $catNonKopi, 'name' => 'Teh Leci Es', 'price' => 10000],
            ['cat' => $catNonKopi, 'name' => 'Mangga Gembira', 'price' => 10000],
            ['cat' => $catNonKopi, 'name' => 'Melon Susu', 'price' => 10000],
            ['cat' => $catNonKopi, 'name' => 'Apel Warkop', 'price' => 10000],
            ['cat' => $catNonKopi, 'name' => 'Leci Yakult', 'price' => 12000],
            
            // MAKANAN
            ['cat' => $catMakanan, 'name' => 'Roti Panggang', 'price' => 5000],
            ['cat' => $catMakanan, 'name' => 'Dimsum', 'price' => 10000],
            ['cat' => $catMakanan, 'name' => 'Indomie', 'price' => 6000],
        ];

        foreach ($products as $p) {
            $product = Product::updateOrCreate(['slug' => Str::slug($p['name'])], [
                'category_id' => $p['cat']->id,
                'name'        => $p['name'],
                'price'       => $p['price'],
                'cost_price'  => $p['price'] * 0.4, // Estimate cost at 40%
                'is_available'=> true,
            ]);

            // Specific Modifiers
            if (str_contains($p['name'], 'Kopi') || str_contains($p['name'], 'Teh')) {
                $product->modifierGroups()->syncWithoutDetaching([$sugarLevel->id]);
            }
            if ($p['name'] === 'Roti Panggang') {
                $product->modifierGroups()->syncWithoutDetaching([$rotiFlavor->id]);
            }
            if ($p['name'] === 'Indomie') {
                $product->modifierGroups()->syncWithoutDetaching([$indomieTopping->id]);
            }
        }

        // Tables
        foreach (range(1, 15) as $i) {
            Table::updateOrCreate(['name' => "Meja {$i}"], [
                'area'     => $i <= 8 ? 'Indoor' : 'Outdoor',
                'capacity' => 4,
                'status'   => 'available',
                'pos_x'    => ($i - 1) % 5 * 120,
                'pos_y'    => floor(($i - 1) / 5) * 120,
            ]);
        }

        // Supplier
        Supplier::updateOrCreate(['name' => 'Warkop Central Supplier'], [
            'contact_person' => 'Bang Haji',
            'phone'          => '081234567890',
            'email'          => 'supply@koetagadjah.com',
            'address'        => 'Pasar Induk Kramat Jati, Jakarta',
        ]);

        $this->command->info('Master data (Warkop Koetagadjah Menu) seeded successfully!');
    }
}
