<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'store_id',
        'code',
        'name',
        'type',
        'parent_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalLine::class);
    }

    public function getBalanceAttribute(): float
    {
        $debit  = $this->journalLines()->sum('debit');
        $credit = $this->journalLines()->sum('credit');

        return in_array($this->type, ['asset', 'expense'])
            ? $debit - $credit
            : $credit - $debit;
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'asset'     => 'Aset',
            'liability' => 'Kewajiban',
            'equity'    => 'Ekuitas',
            'revenue'   => 'Pendapatan',
            'expense'   => 'Beban',
            default     => $this->type,
        };
    }
}
