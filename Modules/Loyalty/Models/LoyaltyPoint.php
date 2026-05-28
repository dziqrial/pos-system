<?php

namespace Modules\Loyalty\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'transaction_id',
        'type',
        'delta',
        'balance',
        'expired_at',
        'note',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
