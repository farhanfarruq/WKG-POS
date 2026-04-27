<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RawMaterial extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unit_id', 'name', 'sku', 'current_stock', 'min_stock', 'cost_per_unit', 'is_active',
    ];

    protected $casts = [
        'current_stock' => 'decimal:3',
        'min_stock'     => 'decimal:3',
        'cost_per_unit' => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function bomRecipes(): HasMany
    {
        return $this->hasMany(BomRecipe::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->min_stock;
    }
}
