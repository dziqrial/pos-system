<?php

namespace Modules\Loyalty\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'store_id',
        'code',
        'name',
        'type',
        'value',
        'min_purchase',
        'max_discount',
        'quota',
        'used_count',
        'is_active',
        'started_at',
        'expired_at',
    ];

    protected $casts = [
        'value'        => 'float',
        'min_purchase' => 'float',
        'max_discount' => 'float',
        'is_active'    => 'boolean',
        'started_at'   => 'date',
        'expired_at'   => 'date',
    ];

    public function usages()
    {
        return $this->hasMany(VoucherUsage::class);
    }

    public function isValid(float $purchaseAmount = 0): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expired_at && $this->expired_at->isPast()) {
            return false;
        }

        if ($this->started_at && $this->started_at->isFuture()) {
            return false;
        }

        if ($this->quota && $this->used_count >= $this->quota) {
            return false;
        }

        if ($purchaseAmount < $this->min_purchase) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percent') {
            return $subtotal * ($this->value / 100);
        }

        return min($this->value, $subtotal);
    }
}
