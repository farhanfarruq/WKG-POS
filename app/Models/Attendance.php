<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'shift_id', 'work_shift_id', 'date', 'clock_in', 'clock_out', 'clock_in_method', 'notes', 'is_late', 'late_minutes'
    ];

    protected $casts = [
        'date'      => 'date',
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
        'is_late'   => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class);
    }

    public function workDurationMinutes(): ?int
    {
        if (!$this->clock_in || !$this->clock_out) return null;
        return $this->clock_in->diffInMinutes($this->clock_out);
    }
}
