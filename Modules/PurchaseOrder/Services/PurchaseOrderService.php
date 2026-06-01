<?php

namespace Modules\PurchaseOrder\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Events\PurchaseOrderReceived;
use Modules\Core\Services\StockService;
use Modules\PurchaseOrder\Models\PoItem;
use Modules\PurchaseOrder\Models\PurchaseOrder;

class PurchaseOrderService
{
    public function __construct(private StockService $stockService) {}

    public function createPO(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $storeId = auth()->user()->store_id;

            // Generate PO code
            $count = PurchaseOrder::where('store_id', $storeId)->count();
            $code  = 'PO-' . now()->format('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['qty_ordered'] * $item['unit_cost'];
            }

            $taxAmount = isset($data['tax_percent']) ? ($subtotal * $data['tax_percent'] / 100) : 0;
            $total     = $subtotal + $taxAmount;

            $po = PurchaseOrder::create([
                'store_id'    => $storeId,
                'supplier_id' => $data['supplier_id'],
                'outlet_id'   => $data['outlet_id'],
                'user_id'     => auth()->id(),
                'code'        => $code,
                'status'      => 'draft',
                'notes'       => $data['notes'] ?? null,
                'subtotal'    => $subtotal,
                'tax_amount'  => $taxAmount,
                'total'       => $total,
            ]);

            foreach ($data['items'] as $item) {
                PoItem::create([
                    'purchase_order_id'  => $po->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'name'               => $item['name'],
                    'qty_ordered'        => $item['qty_ordered'],
                    'qty_received'       => 0,
                    'unit_cost'          => $item['unit_cost'],
                    'subtotal'           => $item['qty_ordered'] * $item['unit_cost'],
                ]);
            }

            return $po;
        });
    }

    public function submitPO(PurchaseOrder $po): void
    {
        if (!$po->isDraft()) {
            throw new \RuntimeException('Hanya PO berstatus draft yang bisa diajukan.');
        }

        $po->update([
            'status'     => 'ordered',
            'ordered_at' => now(),
        ]);
    }

    public function receivePO(PurchaseOrder $po, array $receivedQtys): void
    {
        if (!$po->canReceive()) {
            throw new \RuntimeException('PO ini tidak bisa diterima. Status harus ordered atau partial.');
        }

        DB::transaction(function () use ($po, $receivedQtys) {
            $po->load('items');
            $allReceived = true;

            foreach ($po->items as $item) {
                $receivedQty = (float) ($receivedQtys[$item->id] ?? 0);

                if ($receivedQty <= 0) {
                    if ($item->qty_received < $item->qty_ordered) {
                        $allReceived = false;
                    }
                    continue;
                }

                $newQtyReceived = $item->qty_received + $receivedQty;
                $item->update(['qty_received' => $newQtyReceived]);

                // Update stock via StockService
                $this->stockService->add(
                    $item->product_variant_id,
                    $po->outlet_id,
                    $receivedQty,
                    [
                        'ref_type' => 'purchase_order',
                        'ref_id'   => $po->id,
                        'note'     => "Penerimaan dari PO {$po->code}",
                    ]
                );

                if ($newQtyReceived < $item->qty_ordered) {
                    $allReceived = false;
                }
            }

            $newStatus = $allReceived ? 'received' : 'partial';
            $po->update([
                'status'      => $newStatus,
                'received_at' => now(),
            ]);

            // Fire event untuk modul lain (Accounting, dll)
            event(new PurchaseOrderReceived($po->id));
        });
    }

    public function cancelPO(PurchaseOrder $po): void
    {
        if (!$po->canCancel()) {
            throw new \RuntimeException('PO ini tidak bisa dibatalkan.');
        }

        $po->update(['status' => 'cancelled']);
    }
}
