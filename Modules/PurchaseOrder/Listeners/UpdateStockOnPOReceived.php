<?php

namespace Modules\PurchaseOrder\Listeners;

use Modules\Core\Events\PurchaseOrderReceived;

class UpdateStockOnPOReceived
{
    /**
     * Stok sudah diupdate langsung di PurchaseOrderService::receivePO().
     * Listener ini tersedia untuk integrasi pihak ketiga atau logging tambahan.
     */
    public function handle(PurchaseOrderReceived $event): void
    {
        // Stok diupdate di PurchaseOrderService — tidak ada aksi tambahan di sini
    }
}
