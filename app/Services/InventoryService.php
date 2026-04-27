<?php

namespace App\Services;

use App\Models\Order;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use App\Enums\StockMovementType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Deduct stock for all items in an order based on their BOM recipes.
     *
     * @param Order $order
     * @return void
     */
    public function deductStockForOrder(Order $order): void
    {
        // Prevent double deduction
        $alreadyDeducted = StockMovement::where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->exists();

        if ($alreadyDeducted) {
            Log::info("Stock already deducted for Order #{$order->order_number}. Skipping.");
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($order->items as $item) {
                $product = $item->product;
                if (!$product) continue;

                $recipes = $product->bomRecipes;
                if ($recipes->isEmpty()) {
                    Log::warning("No recipe found for product: {$product->name} (Order #{$order->order_number})");
                    continue;
                }

                foreach ($recipes as $recipe) {
                    $material = $recipe->rawMaterial;
                    if (!$material) {
                        Log::warning("No material linked to recipe for product: {$product->name}");
                        continue;
                    }

                    $deductionQty = (float) $recipe->quantity * (float) $item->quantity;
                    Log::info("Deducting {$deductionQty} from material: {$material->name} (Current: {$material->current_stock})");
                    
                    $stockBefore = (float) $material->current_stock;
                    $stockAfter = $stockBefore - $deductionQty;

                    // Update material stock
                    $material->update([
                        'current_stock' => $stockAfter
                    ]);

                    Log::info("New stock for {$material->name}: {$stockAfter}");

                    // Record movement
                    StockMovement::create([
                        'raw_material_id' => $material->id,
                        'user_id'         => $order->cashier_id ?: null,
                        'type'            => StockMovementType::Out,
                        'quantity'        => -$deductionQty, // Record as negative for 'Out'
                        'stock_before'    => $stockBefore,
                        'stock_after'     => $stockAfter,
                        'reference_type'  => Order::class,
                        'reference_id'    => $order->id,
                        'notes'           => "Auto-deduction for Order #{$order->order_number} - Product: {$product->name}",
                    ]);
                }
            }
            Log::info("Stock deduction completed for Order #{$order->order_number}");
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to deduct stock for Order #{$order->order_number}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Perform stock opname (adjustment) for a raw material.
     *
     * @param int $materialId
     * @param float $actualStock
     * @param string $notes
     * @return void
     */
    public function stockOpname(int $materialId, float $actualStock, string $notes = ''): void
    {
        DB::beginTransaction();
        try {
            $material = RawMaterial::findOrFail($materialId);
            $stockBefore = $material->current_stock;
            $diff = $actualStock - $stockBefore;

            // Update material stock
            $material->update([
                'current_stock' => $actualStock
            ]);

            // Record movement
            StockMovement::create([
                'raw_material_id' => $material->id,
                'type'            => StockMovementType::Adjustment,
                'quantity'        => $diff,
                'stock_before'    => $stockBefore,
                'stock_after'     => $actualStock,
                'notes'           => $notes ?: "Manual Stock Opname / Adjustment",
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to perform stock opname for material ID {$materialId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add stock for all items in a purchase order when it is received.
     *
     * @param \App\Models\PurchaseOrder $po
     * @return void
     */
    public function addStockFromPurchaseOrder(\App\Models\PurchaseOrder $po): void
    {
        $po->load('items.rawMaterial');

        Log::info("InventoryService@addStockFromPurchaseOrder: Processing PO #{$po->po_number} with " . $po->items->count() . " items.");
        DB::beginTransaction();
        try {
            foreach ($po->items as $item) {
                $material = $item->rawMaterial;
                if (!$material) {
                    Log::warning("No material found for PO Item ID: {$item->id}");
                    continue;
                }

                $alreadyReceived = (float) $item->quantity_received;
                $orderedQty = (float) $item->quantity_ordered;
                $additionQty = max($orderedQty - $alreadyReceived, 0);

                if ($additionQty <= 0) {
                    Log::info("Skipping PO Item ID {$item->id} because stock was already received.");
                    continue;
                }

                Log::info("Adding {$additionQty} to material: {$material->name}");

                $stockBefore = $material->current_stock;
                $stockAfter = $stockBefore + $additionQty;

                // Update material stock
                $material->update([
                    'current_stock' => $stockAfter
                ]);

                // Update item received quantity
                $item->update([
                    'quantity_received' => $orderedQty
                ]);

                // Record movement
                StockMovement::create([
                    'raw_material_id' => $material->id,
                    'user_id'         => auth()->id() ?: 1,
                    'type'            => StockMovementType::In,
                    'quantity'        => $additionQty,
                    'stock_before'    => $stockBefore,
                    'stock_after'     => $stockAfter,
                    'reference_type'  => \App\Models\PurchaseOrder::class,
                    'reference_id'    => $po->id,
                    'notes'           => "Received from Purchase Order #{$po->po_number}",
                ]);
            }

            // Update PO received date if not set
            if (!$po->received_at) {
                $po->forceFill(['received_at' => now()])->saveQuietly();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to add stock from Purchase Order #{$po->po_number}: " . $e->getMessage());
            throw $e;
        }
    }
}
