<?php

namespace Modules\Loyalty\Listeners;

use Modules\Core\Events\TransactionVoided;
use Modules\Loyalty\Services\LoyaltyService;

class RollbackPointsOnVoid
{
    public function __construct(private LoyaltyService $loyaltyService) {}

    public function handle(TransactionVoided $event): void
    {
        try {
            $this->loyaltyService->rollbackPoints($event->transaction->id);
        } catch (\Exception) {
            // Jangan biarkan kegagalan rollback memblokir void transaksi
        }
    }
}
