<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Services\InventoryService;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeductInventoryListener
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    public function handle(PaymentCompleted $event): void
    {
        $this->inventoryService->deductStockForOrder($event->order);
    }

    public function failed(PaymentCompleted $event, \Throwable $exception): void
    {
        \Log::error('DeductInventoryListener failed', [
            'order_id' => $event->order->id,
            'error'    => $exception->getMessage(),
        ]);
    }
}
