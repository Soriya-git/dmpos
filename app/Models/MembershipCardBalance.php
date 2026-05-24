<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipCardBalance extends Model
{
    protected $guarded = [];

    protected $casts = [
        'balance' => 'decimal:2',
        'ledger_verified_at' => 'datetime',
    ];

    public function membershipCard(): BelongsTo
    {
        return $this->belongsTo(MembershipCard::class);
    }
}
