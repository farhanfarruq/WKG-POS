<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shift;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class OrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_payment_rejects_underpaid_orders(): void
    {
        $user = User::factory()->create();
        $shift = Shift::create([
            'opened_by' => $user->id,
            'opening_cash' => 100000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $order = Order::create([
            'order_number' => 'TRX-TEST-0001',
            'shift_id' => $shift->id,
            'cashier_id' => $user->id,
            'order_type' => 'takeaway',
            'status' => 'pending',
            'total' => 50000,
        ]);

        try {
            app(OrderService::class)->processPayment($order, [
                ['method' => 'cash', 'amount' => 25000],
            ]);

            $this->fail('Underpaid order was accepted.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }

        $this->assertDatabaseCount('payments', 0);
        $this->assertSame('pending', $order->fresh()->status->value);
    }
}
