<?php

namespace App\Listeners;

use App\Enums\KDSStatus;
use App\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendToKDSListener implements ShouldQueue
{
    public string $queue = 'kds';

    public function handle(OrderCreated $event): void
    {
        $event->order->items()->update([
            'kds_status'  => KDSStatus::Pending->value,
            'kds_sent_at' => now(),
        ]);

        // Broadcast to KDS via WebSocket / polling
        // event(new \App\Events\KDSOrderReceived($event->order));
    }
}
