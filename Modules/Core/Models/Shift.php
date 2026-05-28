<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\System\Models\User;

class Shift extends Model
{
    protected $fillable = [
        'outlet_id',
        'user_id',
        'opened_at',
        'closed_at',
        'cash_start',
        'cash_end',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'opened_at'  => 'datetime',
            'closed_at'  => 'datetime',
            'cash_start' => 'float',
            'cash_end'   => 'float',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
