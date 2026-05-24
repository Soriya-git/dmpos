<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class MembershipCardTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'promotion_amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'exchange_rate_snapshot' => 'decimal:4',
        'amount_usd_equivalent' => 'decimal:2',
        'amount_khr_equivalent' => 'decimal:2',
        'transacted_at' => 'datetime',
        'payload' => 'array',
        'ledger_sequence' => 'integer',
        'signature_version' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new RuntimeException('Membership card transactions are immutable.');
        });

        static::deleting(function (): void {
            throw new RuntimeException('Membership card transactions are immutable.');
        });
    }

    public function membershipCard(): BelongsTo
    {
        return $this->belongsTo(MembershipCard::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
