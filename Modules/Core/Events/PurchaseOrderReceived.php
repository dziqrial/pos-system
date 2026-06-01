<?php

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderReceived
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $purchaseOrderId,
    ) {}
}
