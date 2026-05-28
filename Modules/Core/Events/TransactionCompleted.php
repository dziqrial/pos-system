<?php

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\Transaction;

class TransactionCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Transaction $transaction,
    ) {}
}
