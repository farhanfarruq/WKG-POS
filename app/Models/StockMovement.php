<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $fillable = [
        'raw_material_id', 'user_id', 'type', 'quantity',
        'stock_before', 'stock_after', 'reference_type', 'reference_id', 'notes',
    ];
 
    protected static function booted(): void
    {
        static::creating(function ($movement) {
            if (!$movement->user_id && auth()->check()) {
                $movement->user_id = auth()->id();
            }
        });
    }

    protected $casts = [
        'type'         => StockMovementType::class,
        'quantity'     => 'decimal:3',
        'stock_before' => 'decimal:3',
        'stock_after'  => 'decimal:3',
    ];

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
