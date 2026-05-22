<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Customer|null $customer
 * @property-read DiningSession|null $diningSession
 * @property-read PosTerminal|null $posTerminal
 * @property-read Collection<int, InvoiceLine> $lines
 */
class Invoice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'exchange_rate_snapshot' => 'decimal:4',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'stock_settled_quantity' => 'decimal:4',
        'pos_open_date' => 'date',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'stock_settled_at' => 'datetime',
        'stock_rejected_at' => 'datetime',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** @return BelongsTo<PosTerminal, $this> */
    public function posTerminal(): BelongsTo
    {
        return $this->belongsTo(PosTerminal::class);
    }

    /** @return BelongsTo<DiningSession, $this> */
    public function diningSession(): BelongsTo
    {
        return $this->belongsTo(DiningSession::class);
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return HasMany<InvoiceLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return BelongsTo<User, $this> */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /** @return BelongsTo<User, $this> */
    public function stockSettler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'stock_settled_by');
    }

    /** @return BelongsTo<User, $this> */
    public function stockRejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'stock_rejected_by');
    }

    /** @return BelongsTo<User, $this> */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
