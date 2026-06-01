<?php

namespace Modules\Loyalty\Listeners;

use Illuminate\Support\Facades\DB;
use Modules\Core\Events\TransactionCompleted;
use Modules\Loyalty\Services\LoyaltyService;

class AwardPointsOnTransaction
{
    public function __construct(private LoyaltyService $loyaltyService) {}

    public function handle(TransactionCompleted $event): void
    {
        $transaction = $event->transaction;

        if (!$transaction->customer_id) {
            return;
        }

        $outletStore = $transaction->outlet->store_id ?? null;

        if (!$outletStore) {
            return;
        }

        try {
            $isEnabled = DB::table('store_modules')
                ->join('modules', 'store_modules.module_id', '=', 'modules.id')
                ->where('modules.key', 'loyalty')
                ->where('store_modules.store_id', $outletStore)
                ->where('store_modules.is_enabled', true)
                ->exists();

            if (!$isEnabled) {
                return;
            }

            $this->loyaltyService->awardPoints(
                $transaction->customer_id,
                $transaction->total,
                $transaction->id
            );
        } catch (\Exception) {
            // Jangan biarkan kegagalan loyalty mengganggu transaksi
        }
    }
}
