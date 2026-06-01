<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Events\StockUpdated;
use Modules\Core\Models\Inventory;
use Modules\Core\Models\ProductSerialNumber;
use Modules\Core\Models\ProductVariant;
use Modules\Core\Models\StockMovement;

class StockService
{
    /**
     * Kurangi stok (dipanggil saat transaksi untuk produk normal/bulk).
     */
    public function deduct(int $variantId, int $outletId, float $qty, array $meta = []): void
    {
        DB::transaction(function () use ($variantId, $outletId, $qty, $meta) {
            $inventory = Inventory::where('product_variant_id', $variantId)
                ->where('outlet_id', $outletId)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                throw new \RuntimeException("Inventori tidak ditemukan untuk varian #{$variantId} di outlet #{$outletId}");
            }

            if ($inventory->qty < $qty) {
                throw new \RuntimeException("Stok tidak mencukupi. Tersedia: {$inventory->qty}, Diminta: {$qty}");
            }

            $qtyBefore = $inventory->qty;
            $inventory->decrement('qty', $qty);
            $inventory->refresh();

            StockMovement::create([
                'inventory_id' => $inventory->id,
                'user_id'      => auth()->id(),
                'type'         => 'out',
                'qty'          => -$qty,
                'qty_before'   => $qtyBefore,
                'qty_after'    => $inventory->qty,
                'ref_type'     => $meta['ref_type'] ?? null,
                'ref_id'       => $meta['ref_id'] ?? null,
                'note'         => $meta['note'] ?? null,
                'created_at'   => now(),
            ]);

            event(new StockUpdated($inventory, -$qty, 'out'));
        });
    }

    /**
     * Tambah stok (dipanggil saat PO diterima atau adjustment manual).
     */
    public function add(int $variantId, int $outletId, float $qty, array $meta = []): void
    {
        DB::transaction(function () use ($variantId, $outletId, $qty, $meta) {
            $inventory = Inventory::firstOrCreate(
                ['product_variant_id' => $variantId, 'outlet_id' => $outletId, 'sub_rack_id' => null],
                ['qty' => 0, 'min_qty' => 0]
            );

            // Re-acquire lock after firstOrCreate
            $inventory = Inventory::where('id', $inventory->id)->lockForUpdate()->first();

            $qtyBefore = $inventory->qty;
            $inventory->increment('qty', $qty);
            $inventory->refresh();

            StockMovement::create([
                'inventory_id' => $inventory->id,
                'user_id'      => auth()->id(),
                'type'         => 'in',
                'qty'          => $qty,
                'qty_before'   => $qtyBefore,
                'qty_after'    => $inventory->qty,
                'ref_type'     => $meta['ref_type'] ?? null,
                'ref_id'       => $meta['ref_id'] ?? null,
                'note'         => $meta['note'] ?? null,
                'created_at'   => now(),
            ]);

            event(new StockUpdated($inventory, $qty, 'in'));
        });
    }

    /**
     * Adjustment manual stok (opname).
     */
    public function adjust(int $inventoryId, float $newQty, string $note = ''): void
    {
        DB::transaction(function () use ($inventoryId, $newQty, $note) {
            $inventory = Inventory::lockForUpdate()->findOrFail($inventoryId);

            $qtyBefore = $inventory->qty;
            $diff      = $newQty - $qtyBefore;

            $inventory->update(['qty' => $newQty]);

            StockMovement::create([
                'inventory_id' => $inventory->id,
                'user_id'      => auth()->id(),
                'type'         => 'adjust',
                'qty'          => $diff,
                'qty_before'   => $qtyBefore,
                'qty_after'    => $newQty,
                'ref_type'     => 'manual',
                'ref_id'       => null,
                'note'         => $note ?: 'Penyesuaian stok manual',
                'created_at'   => now(),
            ]);

            event(new StockUpdated($inventory, $diff, 'adjust'));
        });
    }

    /**
     * Khusus produk serial: ambil N kode random, tandai sold.
     * PENTING: gunakan lockForUpdate() untuk hindari race condition.
     *
     * @return array{serial_numbers: string[]}
     */
    public function consumeSerials(int $variantId, int $outletId, int $qty, int $transactionId): array
    {
        return DB::transaction(function () use ($variantId, $outletId, $qty, $transactionId) {
            $serials = ProductSerialNumber::where('product_variant_id', $variantId)
                ->where('outlet_id', $outletId)
                ->where('status', 'available')
                ->inRandomOrder()
                ->limit($qty)
                ->lockForUpdate()
                ->get();

            if ($serials->count() < $qty) {
                throw new \RuntimeException(
                    "Serial number tidak mencukupi. Tersedia: {$serials->count()}, Diminta: {$qty}"
                );
            }

            $codes = [];
            foreach ($serials as $serial) {
                $serial->update([
                    'status'         => 'sold',
                    'transaction_id' => $transactionId,
                    'sold_at'        => now(),
                ]);
                $codes[] = $serial->serial_code;
            }

            return ['serial_numbers' => $codes];
        });
    }

    /**
     * Kembalikan serial ke status available (saat void transaksi).
     */
    public function returnSerials(int $transactionId, int $variantId): void
    {
        ProductSerialNumber::where('transaction_id', $transactionId)
            ->where('product_variant_id', $variantId)
            ->update([
                'status'         => 'available',
                'transaction_id' => null,
                'sold_at'        => null,
            ]);
    }

    /**
     * Khusus produk serial: generate kode baru.
     *
     * @return string[]
     */
    public function generateSerials(int $variantId, int $outletId, int $qty, string $prefix = ''): array
    {
        $generated = [];

        for ($i = 0; $i < $qty; $i++) {
            do {
                $code = strtoupper($prefix . substr(md5(uniqid((string) mt_rand(), true)), 0, 10));
            } while (
                ProductSerialNumber::where('product_variant_id', $variantId)
                    ->where('serial_code', $code)
                    ->exists()
            );

            ProductSerialNumber::create([
                'product_variant_id' => $variantId,
                'outlet_id'          => $outletId,
                'serial_code'        => $code,
                'status'             => 'available',
                'source'             => 'generated',
            ]);

            $generated[] = $code;
        }

        return $generated;
    }

    /**
     * Cek stok tersedia (handle semua tipe produk).
     * - normal/bulk: return inventory.qty
     * - serial: return COUNT serial WHERE status=available
     */
    public function getAvailableStock(int $variantId, int $outletId): float|int
    {
        $variant = ProductVariant::with('product')->find($variantId);

        if (!$variant) {
            return 0;
        }

        if ($variant->product?->stock_type === 'serial') {
            return ProductSerialNumber::where('product_variant_id', $variantId)
                ->where('outlet_id', $outletId)
                ->where('status', 'available')
                ->count();
        }

        $inventory = Inventory::where('product_variant_id', $variantId)
            ->where('outlet_id', $outletId)
            ->first();

        return $inventory?->qty ?? 0;
    }

    /**
     * Transfer stok antar outlet.
     */
    public function transfer(int $variantId, int $fromOutletId, int $toOutletId, float $qty): void
    {
        DB::transaction(function () use ($variantId, $fromOutletId, $toOutletId, $qty) {
            $from = Inventory::where('product_variant_id', $variantId)
                ->where('outlet_id', $fromOutletId)
                ->lockForUpdate()
                ->first();

            if (!$from || $from->qty < $qty) {
                throw new \RuntimeException('Stok tidak mencukupi untuk transfer.');
            }

            $to = Inventory::firstOrCreate(
                ['product_variant_id' => $variantId, 'outlet_id' => $toOutletId, 'sub_rack_id' => null],
                ['qty' => 0, 'min_qty' => 0]
            );
            $to = Inventory::where('id', $to->id)->lockForUpdate()->first();

            $fromBefore = $from->qty;
            $toBefore   = $to->qty;

            $from->decrement('qty', $qty);
            $to->increment('qty', $qty);

            $from->refresh();
            $to->refresh();

            StockMovement::create([
                'inventory_id' => $from->id,
                'user_id'      => auth()->id(),
                'type'         => 'transfer',
                'qty'          => -$qty,
                'qty_before'   => $fromBefore,
                'qty_after'    => $from->qty,
                'note'         => "Transfer ke outlet #{$toOutletId}",
                'created_at'   => now(),
            ]);

            StockMovement::create([
                'inventory_id' => $to->id,
                'user_id'      => auth()->id(),
                'type'         => 'transfer',
                'qty'          => $qty,
                'qty_before'   => $toBefore,
                'qty_after'    => $to->qty,
                'note'         => "Transfer dari outlet #{$fromOutletId}",
                'created_at'   => now(),
            ]);

            event(new StockUpdated($from, -$qty, 'transfer'));
            event(new StockUpdated($to, $qty, 'transfer'));
        });
    }
}
