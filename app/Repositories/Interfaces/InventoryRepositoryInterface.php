<?php

namespace App\Repositories\Interfaces;

use App\Models\RawMaterial;
use Illuminate\Database\Eloquent\Collection;

interface InventoryRepositoryInterface
{
    public function getAll(): Collection;
    public function findById(int $id): ?RawMaterial;
    public function getLowStock(): Collection;
    public function updateStock(int $id, float $quantity): RawMaterial;
    public function recordMovement(array $data): void;
}
