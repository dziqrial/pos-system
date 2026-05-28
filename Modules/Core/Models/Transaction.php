<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\System\Models\User;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'user_id',
        'shift_id',
        'customer_id',
        'code',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'note',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta'            => 'array',
            'subtotal'        => 'float',
            'discount_amount' => 'float',
            'tax_amount'      => 'float',
            'total'           => 'float',
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

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
