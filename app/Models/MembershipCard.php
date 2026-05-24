<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipCard extends Model
{
    protected $guarded = [];

    protected $casts = [
        'issued_date' => 'date',
        'expired_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(MembershipCardBalance::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MembershipCardTransaction::class);
    }
}
