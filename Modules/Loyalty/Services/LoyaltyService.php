<?php

namespace Modules\Loyalty\Services;

use Illuminate\Support\Facades\DB;
use Modules\Loyalty\Models\Customer;
use Modules\Loyalty\Models\LoyaltyPoint;
use Modules\Loyalty\Models\Voucher;
use Modules\Loyalty\Models\VoucherUsage;

class LoyaltyService
{
    // 1 point per Rp 10.000
    private const POINT_PER_RUPIAH = 10000;

    public function awardPoints(int $customerId, float $transactionTotal, ?int $transactionId = null): int
    {
        $points = (int) ($transactionTotal / self::POINT_PER_RUPIAH);

        if ($points <= 0) {
            return 0;
        }

        return DB::transaction(function () use ($customerId, $points, $transactionId) {
            $customer    = Customer::lockForUpdate()->findOrFail($customerId);
            $newBalance  = $customer->total_points + $points;

            $customer->increment('total_points', $points);

            LoyaltyPoint::create([
                'customer_id'    => $customerId,
                'transaction_id' => $transactionId,
                'type'           => 'earn',
                'points'         => $points,
                'balance_after'  => $newBalance,
                'note'           => 'Poin dari transaksi',
                'created_at'     => now(),
            ]);

            return $points;
        });
    }

    public function redeemPoints(int $customerId, int $points, ?int $transactionId = null): void
    {
        DB::transaction(function () use ($customerId, $points, $transactionId) {
            $customer = Customer::lockForUpdate()->findOrFail($customerId);

            if ($customer->total_points < $points) {
                throw new \RuntimeException("Poin tidak mencukupi. Tersedia: {$customer->total_points}");
            }

            $customer->decrement('total_points', $points);
            $newBalance = $customer->fresh()->total_points;

            LoyaltyPoint::create([
                'customer_id'    => $customerId,
                'transaction_id' => $transactionId,
                'type'           => 'redeem',
                'points'         => -$points,
                'balance_after'  => $newBalance,
                'note'           => 'Poin ditukarkan',
                'created_at'     => now(),
            ]);
        });
    }

    public function rollbackPoints(int $transactionId): void
    {
        $earnedPoints = LoyaltyPoint::where('transaction_id', $transactionId)
            ->where('type', 'earn')
            ->get();

        foreach ($earnedPoints as $point) {
            DB::transaction(function () use ($point, $transactionId) {
                $customer = Customer::lockForUpdate()->find($point->customer_id);

                if (!$customer) {
                    return;
                }

                $customer->decrement('total_points', $point->points);
                $newBalance = $customer->fresh()->total_points;

                LoyaltyPoint::create([
                    'customer_id'    => $point->customer_id,
                    'transaction_id' => $transactionId,
                    'type'           => 'adjust',
                    'points'         => -$point->points,
                    'balance_after'  => $newBalance,
                    'note'           => 'Rollback dari void transaksi',
                    'created_at'     => now(),
                ]);
            });
        }
    }

    public function applyVoucher(string $code, float $subtotal, int $storeId): array
    {
        $voucher = Voucher::where('code', $code)
            ->where('store_id', $storeId)
            ->first();

        if (!$voucher || !$voucher->isValid($subtotal)) {
            throw new \RuntimeException('Voucher tidak valid atau sudah kadaluarsa.');
        }

        $discount = $voucher->calculateDiscount($subtotal);

        return ['voucher' => $voucher, 'discount' => $discount];
    }

    public function recordVoucherUsage(int $voucherId, int $transactionId, float $discount, ?int $customerId = null): void
    {
        DB::transaction(function () use ($voucherId, $transactionId, $discount, $customerId) {
            $voucher = Voucher::lockForUpdate()->findOrFail($voucherId);
            $voucher->increment('uses_count');

            VoucherUsage::create([
                'voucher_id'      => $voucherId,
                'transaction_id'  => $transactionId,
                'customer_id'     => $customerId,
                'discount_amount' => $discount,
                'used_at'         => now(),
            ]);
        });
    }
}
