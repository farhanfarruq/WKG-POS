<?php

namespace App\Models;

use App\Enums\KDSStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'unit_price',
        'quantity', 'modifier_price', 'subtotal', 'notes',
        'kds_status', 'kds_sent_at', 'kds_completed_at', 'modifiers',
    ];

    protected $casts = [
        'unit_price'       => 'decimal:2',
        'modifier_price'   => 'decimal:2',
        'subtotal'         => 'decimal:2',
        'kds_status'       => KDSStatus::class,
        'kds_sent_at'      => 'datetime',
        'kds_completed_at' => 'datetime',
        'modifiers'        => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
