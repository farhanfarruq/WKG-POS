<?php

namespace App\DTOs;

readonly class OrderItemDTO
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public ?string $notes = null,
        public array $modifierIds = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            quantity: $data['quantity'],
            notes: $data['notes'] ?? null,
            modifierIds: $data['modifier_ids'] ?? [],
        );
    }
}
