<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Events\TransactionCompleted;
use Modules\Core\Events\TransactionVoided;
use Modules\Core\Models\Outlet;
use Modules\Core\Models\Payment;
use Modules\Core\Models\ProductVariant;
use Modules\Core\Models\Transaction;
use Modules\Core\Models\TransactionItem;

class TransactionService
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly ShiftService $shiftService,
    ) {}

    /**
     * Buat transaksi baru (kasir checkout).
     *
     * $data = [
     *   'outlet_id'       => int,
     *   'items'           => [['variant_id' => int, 'qty' => float, 'discount' => float], ...],
     *   'payments'        => [['method' => string, 'amount' => float, 'reference_no' => ?string], ...],
     *   'discount_amount' => float,
     *   'customer_id'     => ?int,
     *   'note'            => ?string,
     *   'meta'            => ?array,
     * ]
     */
    public function createTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $outlet = Outlet::with('store')->findOrFail($data['outlet_id']);
            $shift  = $this->shiftService->getActiveShift($outlet->id);

            if (!$shift) {
                throw new \RuntimeException('Tidak ada shift yang aktif. Buka shift terlebih dahulu.');
            }

            // --- Build items ---
            $subtotal  = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $variant      = ProductVariant::with('product')->findOrFail($item['variant_id']);
                $discount     = (float) ($item['discount'] ?? 0);
                $itemSubtotal = ($variant->price * (float) $item['qty']) - $discount;
                $subtotal    += $itemSubtotal;

                $itemsData[] = [
                    'variant'   => $variant,
                    'qty'       => (float) $item['qty'],
                    'discount'  => $discount,
                    'subtotal'  => $itemSubtotal,
                ];
            }

            // --- Tax & totals ---
            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $taxPercent     = (float) ($outlet->store->settings['tax_percent'] ?? 0);
            $taxAmount      = ($subtotal - $discountAmount) * ($taxPercent / 100);
            $total          = $subtotal - $discountAmount + $taxAmount;

            // --- Create transaction header ---
            $transaction = Transaction::create([
                'outlet_id'       => $outlet->id,
                'user_id'         => auth()->id(),
                'shift_id'        => $shift->id,
                'customer_id'     => $data['customer_id'] ?? null,
                'code'            => $this->generateCode($outlet->id),
                'status'          => 'pending',
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'      => $taxAmount,
                'total'           => $total,
                'note'            => $data['note'] ?? null,
                'meta'            => $data['meta'] ?? null,
            ]);

            // --- Create items & deduct stock ---
            foreach ($itemsData as $item) {
                $variant  = $item['variant'];
                $itemMeta = [];

                if ($variant->product->stock_type === 'serial') {
                    // Untuk produk serial: consume N kode random
                    $serialResult             = $this->stockService->consumeSerials(
                        $variant->id,
                        $outlet->id,
                        (int) $item['qty'],
                        $transaction->id
                    );
                    $itemMeta['serial_numbers'] = $serialResult['serial_numbers'];
                } else {
                    // Untuk produk normal / bulk: kurangi qty inventory
                    $this->stockService->deduct($variant->id, $outlet->id, $item['qty'], [
                        'ref_type' => 'transaction',
                        'ref_id'   => $transaction->id,
                    ]);
                }

                TransactionItem::create([
                    'transaction_id'     => $transaction->id,
                    'product_variant_id' => $variant->id,
                    'name'               => $variant->product->name
                        . ($variant->product->has_variants ? ' - ' . $variant->name : ''),
                    'qty'                => $item['qty'],
                    'price'              => $variant->price,
                    'cost'               => $variant->cost,
                    'discount_amount'    => $item['discount'],
                    'subtotal'           => $item['subtotal'],
                    'meta'               => $itemMeta ?: null,
                ]);
            }

            // --- Record payments ---
            foreach ($data['payments'] as $payment) {
                Payment::create([
                    'transaction_id' => $transaction->id,
                    'method'         => $payment['method'],
                    'amount'         => (float) $payment['amount'],
                    'reference_no'   => $payment['reference_no'] ?? null,
                    'meta'           => $payment['meta'] ?? null,
                ]);
            }

            // --- Mark as completed ---
            $transaction->update(['status' => 'completed']);
            $transaction->refresh();

            event(new TransactionCompleted($transaction));

            return $transaction->load(['items', 'payments', 'outlet', 'user']);
        });
    }

    /**
     * Void / batalkan transaksi dan kembalikan stok.
     */
    public function voidTransaction(Transaction $transaction, string $reason): Transaction
    {
        return DB::transaction(function () use ($transaction, $reason) {
            if ($transaction->isVoided()) {
                throw new \RuntimeException('Transaksi ini sudah di-void.');
            }

            if (!$transaction->isCompleted()) {
                throw new \RuntimeException('Hanya transaksi yang sudah selesai yang dapat di-void.');
            }

            foreach ($transaction->items as $item) {
                $variant = ProductVariant::with('product')->findOrFail($item->product_variant_id);

                if ($variant->product->stock_type === 'serial') {
                    // Kembalikan serial ke status available
                    $this->stockService->returnSerials($transaction->id, $variant->id);
                } else {
                    // Kembalikan qty ke inventory
                    $this->stockService->add($variant->id, $transaction->outlet_id, $item->qty, [
                        'ref_type' => 'transaction_void',
                        'ref_id'   => $transaction->id,
                        'note'     => "Void transaksi #{$transaction->code}: {$reason}",
                    ]);
                }
            }

            $transaction->update(['status' => 'voided']);
            $transaction->refresh();

            event(new TransactionVoided($transaction));

            return $transaction->fresh(['items', 'payments']);
        });
    }

    /**
     * Generate kode transaksi unik per hari.
     * Format: INV-YYYYMMDD-XXXX
     */
    private function generateCode(int $outletId): string
    {
        $date  = now()->format('Ymd');
        $count = Transaction::whereDate('created_at', now())->count() + 1;
        return 'INV-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
