<?php

namespace App\Repositories\Eloquent;

use App\Models\Shift;
use App\Repositories\Interfaces\ShiftRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class EloquentShiftRepository implements ShiftRepositoryInterface
{
    public function findOpenShift(): ?Shift
    {
        return Shift::where('status', 'open')->latest()->first();
    }

    public function findById(int $id): ?Shift
    {
        return Shift::with(['openedBy', 'closedBy', 'orders'])->find($id);
    }

    public function create(array $data): Shift
    {
        return Shift::create($data);
    }

    public function close(int $id, array $data): Shift
    {
        $shift = Shift::findOrFail($id);
        $shift->update(array_merge($data, [
            'status'    => 'closed',
            'closed_at' => now(),
            'closed_by' => Auth::id(),
        ]));
        return $shift->fresh();
    }
}
