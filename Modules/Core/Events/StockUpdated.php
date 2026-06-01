<?php

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\Inventory;

class StockUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Inventory $inventory,
        public readonly float $qty,
        public readonly string $type,
    ) {}
}
