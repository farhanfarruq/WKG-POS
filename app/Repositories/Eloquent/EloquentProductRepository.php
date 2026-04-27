<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function getAll(array $filters = []): Collection
    {
        return Product::with('category')
            ->when(isset($filters['category_id']), fn ($q) => $q->where('category_id', $filters['category_id']))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->orderBy('sort_order')
            ->get();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Product::with('category')
            ->when(isset($filters['search']), fn ($q) => $q->where('name', 'like', '%' . $filters['search'] . '%'))
            ->when(isset($filters['category_id']), fn ($q) => $q->where('category_id', $filters['category_id']))
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Product
    {
        return Product::with(['category', 'modifierGroups.modifiers', 'bomRecipes.rawMaterial'])
            ->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)->first();
    }

    public function getAvailable(): Collection
    {
        return Product::with(['category', 'modifierGroups.modifiers'])
            ->where('is_available', true)
            ->where('is_active', true)
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->get();
    }

    public function getByCategoryId(int $categoryId): Collection
    {
        return Product::where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        return $product->fresh();
    }

    public function delete(int $id): bool
    {
        return Product::findOrFail($id)->delete();
    }
}
