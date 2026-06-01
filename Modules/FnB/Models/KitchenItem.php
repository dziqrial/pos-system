<?php

namespace Modules\FnB\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenItem extends Model
{
    protected $fillable = [
        'kitchen_order_id',
        'product_variant_id',
        'name',
        'qty',
        'status',
        'notes',
    ];

    protected $casts = [
        'qty' => 'float',
    ];

    public function kitchenOrder()
    {
        return $this->belongsTo(KitchenOrder::class);
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'Menunggu',
            'preparing' => 'Diproses',
            'done'      => 'Selesai',
            default     => $this->status,
        };
    }
}
