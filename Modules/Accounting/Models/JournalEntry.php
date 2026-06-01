<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\System\Models\User;

class JournalEntry extends Model
{
    protected $fillable = [
        'store_id',
        'code',
        'description',
        'date',
        'ref_type',
        'ref_id',
        'status',
        'posted_at',
        'user_id',
    ];

    protected $casts = [
        'date'      => 'date',
        'posted_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isBalanced(): bool
    {
        $debit  = $this->lines()->sum('debit');
        $credit = $this->lines()->sum('credit');

        return abs($debit - $credit) < 0.001; // float comparison
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }
}
