<?php

namespace Modules\System\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\System\Events\ModuleDisabled;
use Modules\System\Events\ModuleEnabled;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ModuleEnabled::class  => [],
        ModuleDisabled::class => [],
    ];
}
