<?php

namespace App\Observers;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if ($order->status === OrderStatus::Completed) {
            Log::info("Order #{$order->order_number} created with status completed. Triggering stock deduction.");
            $this->inventoryService->deductStockForOrder($order);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if the status was changed to 'completed'
        if ($order->isDirty('status') && $order->status === OrderStatus::Completed) {
            Log::info("Order #{$order->order_number} status updated to completed. Triggering stock deduction.");
            $this->inventoryService->deductStockForOrder($order);
        }
    }
}
