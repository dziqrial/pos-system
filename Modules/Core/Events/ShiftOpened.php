<?php

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\Shift;

class ShiftOpened
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Shift $shift,
    ) {}
}
