<?php

namespace Modules\System\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\System\Models\Module;

class ModuleEnabled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Module $module,
        public readonly int $storeId,
    ) {}
}
