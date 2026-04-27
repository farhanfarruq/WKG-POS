<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view_orders', 'manage_orders']);
    }

    public function view(User $user, Order $order): bool
    {
        // Allow if user has KDS view permission or is admin/manager
        if ($user->hasAnyPermission(['view_kds', 'manage_orders']) || 
            $user->hasAnyRole(['admin', 'super_admin', 'manager'])) {
            return true;
        }

        // Otherwise, cashier can only view their own orders
        if ($user->hasRole('kasir')) {
            return $order->cashier_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['create_transaction', 'manage_orders']) || 
               $user->hasAnyRole(['admin', 'super_admin', 'kasir']);
    }

    public function update(User $user, Order $order): bool
    {
        if ($order->isCompleted()) return false;
        if ($user->hasRole('kasir')) {
            return $order->cashier_id === $user->id;
        }
        return $user->hasPermissionTo('manage_orders');
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($order->isCompleted()) return false;
        return $user->hasAnyRole(['admin', 'manager', 'super_admin']);
    }

    public function processPayment(User $user, Order $order): bool
    {
        return $this->update($user, $order);
    }
}
