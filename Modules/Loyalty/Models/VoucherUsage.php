<?php

namespace Modules\Loyalty\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherUsage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'voucher_id',
        'transaction_id',
        'customer_id',
        'discount_amount',
        'used_at',
    ];

    protected $casts = [
        'discount_amount' => 'float',
        'used_at'         => 'datetime',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
