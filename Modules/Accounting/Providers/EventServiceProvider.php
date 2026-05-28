<?php

namespace Modules\Accounting\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Accounting\Listeners\CreateJournalEntry;
use Modules\Core\Events\TransactionCompleted;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TransactionCompleted::class => [
            CreateJournalEntry::class,
        ],
    ];
}
