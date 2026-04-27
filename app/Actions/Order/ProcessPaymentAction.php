<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Services\OrderService;

class ProcessPaymentAction
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function execute(Order $order, array $payments): Order
    {
        $totalPaid = collect($payments)->sum('amount');
        abort_if(round($totalPaid, 0) < round($order->total, 0), 422, "Pembayaran kurang. Total: {$order->total}, Dibayar: {$totalPaid}");

        return $this->orderService->processPayment($order, $payments);
    }
}
