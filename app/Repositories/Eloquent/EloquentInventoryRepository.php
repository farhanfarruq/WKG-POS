<?php

namespace App\Repositories\Eloquent;

use App\Models\RawMaterial;
use App\Models\StockMovement;
use App\Repositories\Interfaces\InventoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentInventoryRepository implements InventoryRepositoryInterface
{
    public function getAll(): Collection
    {
        return RawMaterial::with('unit')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?RawMaterial
    {
        return RawMaterial::with(['unit', 'movements' => fn ($q) => $q->latest()->limit(20)])
            ->find($id);
    }

    public function getLowStock(): Collection
    {
        return RawMaterial::with('unit')
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->where('is_active', true)
            ->get();
    }

    public function updateStock(int $id, float $quantity): RawMaterial
    {
        $material = RawMaterial::lockForUpdate()->findOrFail($id);
        $material->increment('current_stock', $quantity);
        return $material->fresh();
    }

    public function recordMovement(array $data): void
    {
        StockMovement::create($data);
    }
}
