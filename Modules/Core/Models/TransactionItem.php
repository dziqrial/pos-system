<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'product_variant_id',
        'name',
        'qty',
        'price',
        'cost',
        'discount_amount',
        'subtotal',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'qty'             => 'float',
            'price'           => 'float',
            'cost'            => 'float',
            'discount_amount' => 'float',
            'subtotal'        => 'float',
            'meta'            => 'array',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
