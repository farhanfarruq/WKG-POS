<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number', 'supplier_id', 'created_by', 'approved_by',
        'status', 'ordered_at', 'expected_at', 'received_at', 'total_amount', 'notes',
    ];
 
    protected static function booted(): void
    {
        static::creating(function ($po) {
            if (!$po->created_by && auth()->check()) {
                $po->created_by = auth()->id();
            }
 
            if (!$po->po_number) {
                $po->po_number = static::generatePONumber();
            }
        });
    }

    protected $casts = [
        'ordered_at'   => 'datetime',
        'expected_at'  => 'datetime',
        'received_at'  => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public static function generatePONumber(): string
    {
        $prefix = 'PO-' . now()->format('Ymd');
        $last = static::where('po_number', 'like', $prefix . '%')->orderByDesc('id')->first();
        $seq = $last ? (int) substr($last->po_number, -4) + 1 : 1;
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
