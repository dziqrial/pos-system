<?php

namespace Modules\FnB\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Outlet;

class KitchenOrder extends Model
{
    protected $fillable = [
        'transaction_id',
        'outlet_id',
        'table_id',
        'status',
        'notes',
        'ordered_at',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function items()
    {
        return $this->hasMany(KitchenItem::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'Menunggu',
            'preparing' => 'Diproses',
            'ready'     => 'Siap',
            'served'    => 'Tersaji',
            default     => $this->status,
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'pending'   => 'yellow',
            'preparing' => 'blue',
            'ready'     => 'green',
            'served'    => 'gray',
            default     => 'gray',
        };
    }
}
