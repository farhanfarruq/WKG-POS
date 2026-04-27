<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;
    public function findByOrderNumber(string $orderNumber): ?Order;
    public function getByShift(int $shiftId): Collection;
    public function getPendingForKDS(): Collection;
    public function create(array $data): Order;
    public function update(int $id, array $data): Order;
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;
}
