<?php

namespace App\DTOs;

use App\Enums\OrderType;

readonly class OrderDTO
{
    public function __construct(
        public int $shiftId,
        public int $cashierId,
        public OrderType $orderType,
        public ?int $tableId = null,
        public ?string $customerName = null,
        public ?string $customerPhone = null,
        public ?string $notes = null,
        public ?int $discountId = null,
    ) {}

    public static function fromRequest(array $data, int $cashierId, int $shiftId): self
    {
        return new self(
            shiftId: $shiftId,
            cashierId: $cashierId,
            orderType: OrderType::from($data['order_type'] ?? 'dine_in'),
            tableId: $data['table_id'] ?? null,
            customerName: $data['customer_name'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
            notes: $data['notes'] ?? null,
            discountId: $data['discount_id'] ?? null,
        );
    }
}
