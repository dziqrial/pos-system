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
        'address',
        'birth_date',
        'total_points',
        'is_active',
    ];

    protected $casts = [
        'birth_date'   => 'date',
        'is_active'    => 'boolean',
        'total_points' => 'integer',
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
