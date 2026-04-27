<?php

namespace App\Http\Controllers\API;

use App\Actions\Order\CreateOrderAction;
use App\Actions\Order\ProcessPaymentAction;
use App\DTOs\OrderItemDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\AddItemRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\ProcessPaymentRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use HasApiResponse;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly CreateOrderAction $createOrderAction,
        private readonly ProcessPaymentAction $processPaymentAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $orders = $this->orderService
            ->getRepository()
            ->paginate(20, $request->only(['status', 'date', 'shift_id']));

        return $this->paginatedResponse($orders);
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $order = $this->createOrderAction->execute(
            $request->validated(),
            $request->user()->id
        );

        return $this->successResponse($order->load('items'), 'Order berhasil dibuat.', 201);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return $this->successResponse(
            $order->load(['items.product', 'payments', 'cashier', 'table', 'discount'])
        );
    }

    public function addItem(AddItemRequest $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $dto = OrderItemDTO::fromArray($request->validated());
        $order = $this->orderService->addItem($order, $dto);

        return $this->successResponse($order->load('items'), 'Item ditambahkan.');
    }

    public function removeItem(Order $order, int $itemId): JsonResponse
    {
        $this->authorize('update', $order);

        $order = $this->orderService->removeItem($order, $itemId);

        return $this->successResponse($order->load('items'), 'Item dihapus.');
    }

    public function hold(Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $order = $this->orderService->holdOrder($order);

        return $this->successResponse($order, 'Order ditahan.');
    }

    public function checkout(ProcessPaymentRequest $request, Order $order): JsonResponse
    {
        $this->authorize('processPayment', $order);

        $order = $this->processPaymentAction->execute($order, $request->validated()['payments']);

        return $this->successResponse(
            $order->load(['items.product', 'payments', 'cashier']),
            'Pembayaran berhasil.'
        );
    }

    public function receipt(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['items.product', 'payments', 'cashier', 'table', 'shift']);

        return $this->successResponse([
            'order_number'  => $order->order_number,
            'date'          => $order->created_at->format('d/m/Y H:i'),
            'cashier'       => $order->cashier->name,
            'table'         => $order->table?->name,
            'order_type'    => $order->order_type->label(),
            'items'         => $order->items->map(fn ($i) => [
                'name'         => $i->product_name,
                'qty'          => $i->quantity,
                'unit_price'   => $i->unit_price,
                'modifier_price'=> $i->modifier_price,
                'subtotal'     => $i->subtotal,
                'notes'        => $i->notes,
                'modifiers'    => $i->modifiers,
            ]),
            'subtotal'        => $order->subtotal,
            'discount'        => $order->discount_amount,
            'tax'             => $order->tax_amount,
            'service_charge'  => $order->service_charge,
            'total'           => $order->total,
            'paid'            => $order->paid_amount,
            'change'          => $order->change_amount,
            'payments'        => $order->payments->map(fn ($p) => [
                'method' => $p->method->label(),
                'amount' => $p->amount,
            ]),
        ]);
    }
}
