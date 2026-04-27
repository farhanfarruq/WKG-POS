<?php

namespace App\Http\Controllers\API;

use App\Enums\KDSStatus;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KDSController extends Controller
{
    use HasApiResponse;

    public function __construct(private readonly OrderRepositoryInterface $orderRepository) {}

    public function index(): JsonResponse
    {
        $orders = $this->orderRepository->getPendingForKDS();

        $formatted = $orders->map(function ($order) {
            return [
                'id'             => $order->id,
                'receipt_number' => $order->order_number,
                'table_id'       => $order->table_id,
                'customer_name'  => $order->customer_name,
                'order_type'     => $order->order_type->value,
                'status'         => $order->status->value,
                'created_at'     => $order->created_at->toIso8601String(),
                'items'          => $order->items->map(fn($item) => [
                    'id'         => $item->id,
                    'product'    => [
                        'name' => $item->product->name,
                    ],
                    'quantity'   => $item->quantity,
                    'status'     => $item->kds_status,
                ]),
            ];
        });

        return $this->successResponse($formatted);
    }

    public function updateItemStatus(Request $request, int $orderId, int $itemId): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:processing,completed'],
        ]);

        $item = \App\Models\OrderItem::where('order_id', $orderId)
            ->findOrFail($itemId);

        $newStatus = KDSStatus::from($request->status);

        $updates = ['kds_status' => $newStatus->value];

        if ($newStatus === KDSStatus::Completed) {
            $updates['kds_completed_at'] = now();

            // Check if all items in order are completed
            $order = $item->order;
            $pendingItems = $order->items()
                ->where('kds_status', '!=', KDSStatus::Completed->value)
                ->where('id', '!=', $itemId)
                ->count();

            if ($pendingItems === 0) {
                $order->update(['status' => \App\Enums\OrderStatus::Ready->value]);
            }
        }

        $item->update($updates);

        return $this->successResponse($item, 'Status diperbarui.');
    }

    public function markOrderComplete(int $orderId): JsonResponse
    {
        $order = \App\Models\Order::findOrFail($orderId);

        $order->items()->update([
            'kds_status'      => KDSStatus::Completed->value,
            'kds_completed_at'=> now(),
        ]);

        $order->update(['status' => \App\Enums\OrderStatus::Ready->value]);

        return $this->successResponse($order, 'Pesanan siap diantar.');
    }

    public function serveOrder(int $orderId): JsonResponse
    {
        $order = \App\Models\Order::findOrFail($orderId);
        $order->update(['status' => \App\Enums\OrderStatus::Completed->value]);

        return $this->successResponse($order, 'Pesanan telah diantar.');
    }
}
