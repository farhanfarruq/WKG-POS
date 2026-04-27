<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateReceiptListener implements ShouldQueue
{
    public string $queue = 'receipts';

    public function handle(PaymentCompleted $event): void
    {
        $order = $event->order;

        // Receipt data is ready in the order object
        // Future: push to thermal printer queue, generate PDF, etc.
        \Log::info("Receipt ready for Order #{$order->order_number}", [
            'total'    => $order->total,
            'items'    => $order->items->count(),
            'cashier'  => $order->cashier->name,
        ]);
    }
}
