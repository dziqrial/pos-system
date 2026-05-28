<?php

namespace Modules\PurchaseOrder\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Core\Events\PurchaseOrderReceived;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PurchaseOrderReceived::class => [],
    ];
}
