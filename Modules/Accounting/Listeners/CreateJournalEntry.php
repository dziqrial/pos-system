<?php

namespace Modules\Accounting\Listeners;

use Modules\Accounting\Services\AccountingService;
use Modules\Core\Events\TransactionCompleted;

class CreateJournalEntry
{
    public function __construct(private AccountingService $accountingService) {}

    public function handle(TransactionCompleted $event): void
    {
        try {
            $this->accountingService->createJournalFromTransaction($event->transaction);
        } catch (\Exception) {
            // Jangan biarkan kegagalan accounting mengganggu transaksi
        }
    }
}
