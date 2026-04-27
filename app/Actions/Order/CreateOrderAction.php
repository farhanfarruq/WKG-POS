<?php

namespace App\Actions\Order;

use App\DTOs\OrderDTO;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\ShiftService;

class CreateOrderAction
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly ShiftService $shiftService,
    ) {}

    public function execute(array $data, int $cashierId): Order
    {
        $shift = $this->shiftService->getCurrentShift();
        abort_unless($shift, 422, 'Tidak ada shift aktif. Buka shift terlebih dahulu.');

        $dto = OrderDTO::fromRequest($data, $cashierId, $shift->id);

        return $this->orderService->createOrder($dto);
    }
}
