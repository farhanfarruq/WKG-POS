<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    protected $fillable = [
        'name', 'area', 'capacity', 'status', 'pos_x', 'pos_y', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function activeOrder()
    {
        return $this->orders()->whereNotIn('status', ['completed', 'cancelled'])->latest()->first();
    }
}
