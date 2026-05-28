<?php

namespace Modules\FnB\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Outlet;

class Table extends Model
{
    protected $table = 'tables';

    protected $fillable = [
        'store_id',
        'outlet_id',
        'name',
        'capacity',
        'status',
        'qr_code',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function kitchenOrders()
    {
        return $this->hasMany(KitchenOrder::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'available' => 'Tersedia',
            'occupied'  => 'Terisi',
            'reserved'  => 'Dipesan',
            default     => $this->status,
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'available' => 'green',
            'occupied'  => 'red',
            'reserved'  => 'yellow',
            default     => 'gray',
        };
    }
}
