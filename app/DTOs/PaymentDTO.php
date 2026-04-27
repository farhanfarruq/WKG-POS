<?php

namespace App\DTOs;

use App\Enums\PaymentMethod;

readonly class PaymentDTO
{
    public function __construct(
        public int $orderId,
        public PaymentMethod $method,
        public float $amount,
        public ?string $referenceNumber = null,
        public array $meta = [],
    ) {}

    public static function fromArray(array $data, int $orderId): self
    {
        return new self(
            orderId: $orderId,
            method: PaymentMethod::from($data['method']),
            amount: (float) $data['amount'],
            referenceNumber: $data['reference_number'] ?? null,
            meta: $data['meta'] ?? [],
        );
    }
}
