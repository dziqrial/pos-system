<?php

namespace Modules\Loyalty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'phone',
        'email',
        'birth_date',
        'tier',
        'total_spend',
        'points_balance',
    ];

    protected $casts = [
        'birth_date'     => 'date',
        'total_spend'    => 'float',
        'points_balance' => 'integer',
    ];

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function voucherUsages()
    {
        return $this->hasMany(VoucherUsage::class);
    }
}
