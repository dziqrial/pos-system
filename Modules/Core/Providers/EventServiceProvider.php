<?php

namespace Modules\Core\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Core\Events\PurchaseOrderReceived;
use Modules\Core\Events\ShiftClosed;
use Modules\Core\Events\ShiftOpened;
use Modules\Core\Events\StockUpdated;
use Modules\Core\Events\TransactionCompleted;
use Modules\Core\Events\TransactionVoided;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TransactionCompleted::class  => [],
        TransactionVoided::class     => [],
        StockUpdated::class          => [],
        ShiftOpened::class           => [],
        ShiftClosed::class           => [],
        PurchaseOrderReceived::class => [],
    ];
}
