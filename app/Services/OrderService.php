<?php

namespace App\Services;

use App\DTOs\OrderDTO;
use App\DTOs\OrderItemDTO;
use App\DTOs\PaymentDTO;
use App\Enums\KDSStatus;
use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Events\PaymentCompleted;
use App\Models\Modifier;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function getRepository(): OrderRepositoryInterface
    {
        return $this->orderRepository;
    }


    public function createOrder(OrderDTO $dto): Order
    {
        $order = $this->orderRepository->create([
            'order_number'  => Order::generateOrderNumber(),
            'shift_id'      => $dto->shiftId,
            'cashier_id'    => $dto->cashierId,
            'table_id'      => $dto->tableId,
            'discount_id'   => $dto->discountId,
            'order_type'    => $dto->orderType->value,
            'status'        => OrderStatus::Pending->value,
            'customer_name' => $dto->customerName,
            'customer_phone'=> $dto->customerPhone,
            'notes'         => $dto->notes,
        ]);

        event(new OrderCreated($order));

        return $order;
    }

    public function addItem(Order $order, OrderItemDTO $dto): Order
    {
        abort_if($order->status === OrderStatus::Completed, 422, 'Order sudah selesai.');
        abort_if($order->status === OrderStatus::Cancelled, 422, 'Order sudah dibatalkan.');

        $product = Product::findOrFail($dto->productId);

        // Calculate modifier price
        $modifierPrice = 0;
        $modifierSnapshot = [];

        if (!empty($dto->modifierIds)) {
            $modifiers = Modifier::whereIn('id', $dto->modifierIds)->get();
            $modifierPrice = $modifiers->sum('additional_price');
            $modifierSnapshot = $modifiers->map(fn ($m) => [
                'id'   => $m->id,
                'name' => $m->name,
                'price'=> $m->additional_price,
            ])->toArray();
        }

        $unitPrice = $product->price;
        $subtotal  = ($unitPrice + $modifierPrice) * $dto->quantity;

        $order->items()->create([
            'product_id'     => $product->id,
            'product_name'   => $product->name,
            'unit_price'     => $unitPrice,
            'quantity'       => $dto->quantity,
            'modifier_price' => $modifierPrice,
            'subtotal'       => $subtotal,
            'notes'          => $dto->notes,
            'kds_status'     => KDSStatus::Pending->value,
            'modifiers'      => $modifierSnapshot ?: null,
        ]);

        return $this->recalculateOrder($order);
    }

    public function removeItem(Order $order, int $itemId): Order
    {
        $item = $order->items()->findOrFail($itemId);
        $item->delete();
        return $this->recalculateOrder($order);
    }

    public function applyDiscount(Order $order, int $discountId): Order
    {
        $order->update(['discount_id' => $discountId]);
        return $this->recalculateOrder($order->fresh());
    }

    public function holdOrder(Order $order): Order
    {
        $order->update(['status' => OrderStatus::Held->value]);
        return $order;
    }

    public function recalculateOrder(Order $order): Order
    {
        $order->load('items', 'discount');

        $subtotal = $order->items->sum('subtotal');

        // Discount
        $discountAmount = 0;
        if ($order->discount && $order->discount->isValid()) {
            $discountAmount = $order->discount->calculateDiscount($subtotal);
        }

        // Tax (PPN 11% example - can be made configurable)
        $taxableAmount = $subtotal - $discountAmount;
        $taxRate       = config('pos.tax_rate', 0.11);
        $taxAmount     = round($taxableAmount * $taxRate, 0);

        // Service charge
        $serviceRate   = config('pos.service_rate', 0.05);
        $serviceCharge = round($taxableAmount * $serviceRate, 0);

        $total = $taxableAmount + $taxAmount + $serviceCharge;

        $order->update([
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount'      => $taxAmount,
            'service_charge'  => $serviceCharge,
            'total'           => round($total, 0),
        ]);

        return $order->fresh('items');
    }

    public function processPayment(Order $order, array $payments): Order
    {
        abort_if($order->status === OrderStatus::Completed, 422, 'Order sudah selesai.');

        DB::transaction(function () use ($order, $payments) {
            $totalPaid = 0;

            foreach ($payments as $paymentData) {
                $dto = PaymentDTO::fromArray($paymentData, $order->id);

                $order->payments()->create([
                    'method'           => $dto->method->value,
                    'amount'           => $dto->amount,
                    'reference_number' => $dto->referenceNumber,
                    'status'           => 'success',
                    'meta'             => $dto->meta ?: null,
                ]);

                $totalPaid += $dto->amount;
            }

            $change = max(0, $totalPaid - $order->total);

            $order->update([
                'paid_amount'   => $totalPaid,
                'change_amount' => $change,
                'status'        => OrderStatus::Processing->value,
            ]);

            // Mark table as available again
            if ($order->table_id) {
                $order->table()->update(['status' => 'available']);
            }
        });

        event(new PaymentCompleted($order->fresh(['items', 'payments'])));

        return $order->fresh(['items', 'payments']);
    }

    public function splitBill(Order $order, array $splits): array
    {
        // splits = [{ item_ids: [...], payment: [{method, amount}] }]
        $orders = [];

        DB::transaction(function () use ($order, $splits, &$orders) {
            foreach ($splits as $index => $split) {
                $splitOrder = $this->orderRepository->create([
                    'order_number'  => Order::generateOrderNumber(),
                    'shift_id'      => $order->shift_id,
                    'cashier_id'    => $order->cashier_id,
                    'table_id'      => $order->table_id,
                    'order_type'    => $order->order_type->value,
                    'status'        => OrderStatus::Pending->value,
                    'customer_name' => $order->customer_name . " (Split " . ($index + 1) . ")",
                    'is_split'      => true,
                ]);

                // Move items to split order
                $order->items()
                    ->whereIn('id', $split['item_ids'])
                    ->update(['order_id' => $splitOrder->id]);

                $splitOrder = $this->recalculateOrder($splitOrder->fresh('items'));
                $splitOrder = $this->processPayment($splitOrder, $split['payments']);

                $orders[] = $splitOrder;
            }

            // Cancel original order
            $order->update(['status' => OrderStatus::Cancelled->value]);
        });

        return $orders;
    }
}
