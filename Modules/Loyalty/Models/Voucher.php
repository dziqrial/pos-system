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
        'max_uses',
        'uses_count',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'value'        => 'float',
        'min_purchase' => 'float',
        'is_active'    => 'boolean',
        'starts_at'    => 'datetime',
        'expires_at'   => 'datetime',
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

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->max_uses && $this->uses_count >= $this->max_uses) {
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
