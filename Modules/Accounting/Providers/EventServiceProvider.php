<?php

namespace Modules\Accounting\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Core\Events\TransactionCompleted;
use Modules\Core\Events\TransactionVoided;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TransactionCompleted::class => [],
        TransactionVoided::class    => [],
    ];
}
