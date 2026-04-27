<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'shift_id', 'cashier_id', 'table_id', 'discount_id',
        'order_type', 'status', 'customer_name', 'customer_phone', 'notes',
        'subtotal', 'discount_amount', 'tax_amount', 'service_charge',
        'total', 'paid_amount', 'change_amount', 'is_split',
    ];

    protected $casts = [
        'order_type'      => OrderType::class,
        'status'          => OrderStatus::class,
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'service_charge'  => 'decimal:2',
        'total'           => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'change_amount'   => 'decimal:2',
        'is_split'        => 'boolean',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === OrderStatus::Completed;
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'TRX-' . now()->format('Ymd');
        $lastOrder = static::where('order_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $seq = $lastOrder
            ? (int) substr($lastOrder->order_number, -4) + 1
            : 1;

        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
