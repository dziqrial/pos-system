<?php

namespace Modules\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'is_core',
        'is_active',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'is_core'   => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function storeModules(): HasMany
    {
        return $this->hasMany(StoreModule::class);
    }
}
