<?php

namespace App\Repositories\Eloquent;

use App\Enums\KDSStatus;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function findById(int $id): ?Order
    {
        return Order::with(['items.product', 'payments', 'cashier', 'table', 'discount'])
            ->find($id);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::with(['items.product', 'payments'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function getByShift(int $shiftId): Collection
    {
        return Order::with(['items', 'payments'])
            ->where('shift_id', $shiftId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getPendingForKDS(): Collection
    {
        return Order::with('items.product')
            ->where(function($q) {
                // Pending or Processing items
                $q->whereHas('items', fn ($q) => $q->whereIn('kds_status', [
                    KDSStatus::Pending->value,
                    KDSStatus::Processing->value,
                ]))
                // OR Order is Ready to be served
                ->orWhere('status', \App\Enums\OrderStatus::Ready->value)
                // OR Order was Completed Today (for history)
                ->orWhere(function($q) {
                    $q->where('status', \App\Enums\OrderStatus::Completed->value)
                      ->whereDate('created_at', \Carbon\Carbon::today());
                });
            })
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at')
            ->get();
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(int $id, array $data): Order
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        return $order->fresh(['items', 'payments']);
    }

    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        return Order::with(['cashier', 'table', 'items'])
            ->when(isset($filters['shift_id']), fn ($q) => $q->where('shift_id', $filters['shift_id']))
            ->when(isset($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['date']), fn ($q) => $q->whereDate('created_at', $filters['date']))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
