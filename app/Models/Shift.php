<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'opened_by', 'closed_by', 'opening_cash', 'closing_cash',
        'expected_cash', 'cash_difference', 'opened_at', 'closed_at', 'notes', 'status',
    ];

    protected $casts = [
        'opening_cash'    => 'decimal:2',
        'closing_cash'    => 'decimal:2',
        'expected_cash'   => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'opened_at'       => 'datetime',
        'closed_at'       => 'datetime',
    ];

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function totalSales(): float
    {
        return $this->orders()->whereIn('status', ['processing', 'ready', 'completed'])->sum('total');
    }

    public function totalCashSales(): float
    {
        return $this->orders()
            ->whereIn('status', ['processing', 'ready', 'completed'])
            ->whereHas('payments', fn ($q) => $q->where('method', 'cash'))
            ->with('payments')
            ->get()
            ->sum(fn ($o) => $o->payments->where('method', 'cash')->sum('amount'));
    }
}
