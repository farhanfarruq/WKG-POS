<?php

namespace App\Policies;

use App\Models\User;

class InventoryPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasPermissionTo('manage_inventory');
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_inventory', 'view_inventory']);
    }
}
