<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\System\Models\User;

class StockMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'inventory_id',
        'user_id',
        'type',
        'qty',
        'qty_before',
        'qty_after',
        'ref_type',
        'ref_id',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'qty'        => 'float',
            'qty_before' => 'float',
            'qty_after'  => 'float',
            'created_at' => 'datetime',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
