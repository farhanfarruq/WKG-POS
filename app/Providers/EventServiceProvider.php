<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\PaymentCompleted;
use App\Listeners\DeductInventoryListener;
use App\Listeners\GenerateReceiptListener;
use App\Listeners\SendToKDSListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderCreated::class => [
            SendToKDSListener::class,
        ],
        PaymentCompleted::class => [
            DeductInventoryListener::class,
            GenerateReceiptListener::class,
        ],
    ];
}
