<?php

namespace Modules\Loyalty\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Core\Events\TransactionCompleted;
use Modules\Core\Events\TransactionVoided;
use Modules\Loyalty\Listeners\AwardPointsOnTransaction;
use Modules\Loyalty\Listeners\RollbackPointsOnVoid;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TransactionCompleted::class => [
            AwardPointsOnTransaction::class,
        ],
        TransactionVoided::class => [
            RollbackPointsOnVoid::class,
        ],
    ];
}
