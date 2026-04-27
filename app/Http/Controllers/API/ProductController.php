<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use HasApiResponse;

    public function __construct(private readonly ProductRepositoryInterface $productRepository) {}

    public function menu(): JsonResponse
    {
        // Grouped by category for POS display
        $products = $this->productRepository->getAvailable();

        $grouped = $products->groupBy('category_id')->map(fn ($items, $categoryId) => [
            'category'  => $items->first()->category->name,
            'category_id' => $categoryId,
            'products'  => $items->map(fn ($p) => [
                'id'             => $p->id,
                'name'           => $p->name,
                'sku'            => $p->sku,
                'price'          => $p->price,
                'image'          => $p->image,
                'image_url'      => $p->image
                    ? route('product-images.show', ['path' => $p->image])
                    : null,
                'modifier_groups'=> $p->modifierGroups->map(fn ($mg) => [
                    'id'          => $mg->id,
                    'name'        => $mg->name,
                    'is_required' => $mg->is_required,
                    'is_multiple' => $mg->is_multiple,
                    'min_select'  => $mg->min_select,
                    'max_select'  => $mg->max_select,
                    'modifiers'   => $mg->modifiers->where('is_active', true)->map(fn ($m) => [
                        'id'    => $m->id,
                        'name'  => $m->name,
                        'price' => $m->additional_price,
                    ])->values(),
                ]),
            ])->values(),
        ])->values();

        return $this->successResponse($grouped);
    }
}
