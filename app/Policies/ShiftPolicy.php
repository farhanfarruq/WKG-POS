<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;

class ShiftPolicy
{
    public function open(User $user): bool
    {
        return $user->hasPermissionTo('manage_shifts');
    }

    public function close(User $user, Shift $shift): bool
    {
        return $user->hasPermissionTo('close_shift');
    }

    public function viewReports(User $user, Shift $shift): bool
    {
        return $user->hasPermissionTo('view_reports') || $user->hasPermissionTo('close_shift');
    }
}
