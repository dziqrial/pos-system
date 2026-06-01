<?php

namespace Modules\PurchaseOrder\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Outlet;
use Modules\Core\Models\Supplier;
use Modules\System\Models\User;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'store_id',
        'supplier_id',
        'outlet_id',
        'user_id',
        'code',
        'status',
        'notes',
        'subtotal',
        'tax_amount',
        'total',
        'ordered_at',
        'received_at',
    ];

    protected $casts = [
        'subtotal'    => 'float',
        'tax_amount'  => 'float',
        'total'       => 'float',
        'ordered_at'  => 'datetime',
        'received_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PoItem::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isOrdered(): bool
    {
        return $this->status === 'ordered';
    }

    public function canReceive(): bool
    {
        return in_array($this->status, ['ordered', 'partial']);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['draft', 'ordered']);
    }
}
