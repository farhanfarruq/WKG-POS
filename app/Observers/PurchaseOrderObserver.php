<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Log;

class PurchaseOrderObserver
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Handle the PurchaseOrder "updated" event.
     */
    public function updated(PurchaseOrder $purchaseOrder): void
    {
        Log::info("PurchaseOrderObserver@updated: PO #{$purchaseOrder->po_number}, Status: {$purchaseOrder->status}, Was Changed: " . json_encode($purchaseOrder->getChanges()));

        // Check if the status was changed to 'received'
        if ($purchaseOrder->wasChanged('status') && $purchaseOrder->status === 'received') {
            Log::info("Status changed to 'received'. Triggering stock addition for PO #{$purchaseOrder->po_number}. Items count: " . $purchaseOrder->items->count());
            $this->inventoryService->addStockFromPurchaseOrder($purchaseOrder);
        }
    }

    /**
     * Handle the PurchaseOrder "created" event.
     */
    public function created(PurchaseOrder $purchaseOrder): void
    {
        Log::info("PurchaseOrderObserver@created: PO #{$purchaseOrder->po_number}, Status: {$purchaseOrder->status}");

        if ($purchaseOrder->status === 'received') {
            Log::info("PO created with status 'received'. Triggering stock addition for PO #{$purchaseOrder->po_number}.");
            $this->inventoryService->addStockFromPurchaseOrder($purchaseOrder);
        }
    }
}
