<?php

namespace Modules\PurchaseOrder\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\ProductVariant;

class PoItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_variant_id',
        'name',
        'qty_ordered',
        'qty_received',
        'unit_cost',
        'subtotal',
    ];

    protected $casts = [
        'qty_ordered'  => 'float',
        'qty_received' => 'float',
        'unit_cost'    => 'float',
        'subtotal'     => 'float',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function remainingQty(): float
    {
        return $this->qty_ordered - $this->qty_received;
    }
}
