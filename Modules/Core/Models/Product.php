<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\System\Models\Store;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'barcode',
        'description',
        'image',
        'has_variants',
        'stock_type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'has_variants' => 'boolean',
            'is_active'    => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->where('is_active', true)->oldestOfMany();
    }

    public function isSerial(): bool
    {
        return $this->stock_type === 'serial';
    }

    public function isBulk(): bool
    {
        return $this->stock_type === 'bulk';
    }

    public function isNormal(): bool
    {
        return $this->stock_type === 'normal';
    }
}
