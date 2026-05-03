<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Branch|null $branch
 * @property-read PosTerminal|null $posTerminal
 * @property-read User|null $opener
 * @property-read User|null $closer
 */
class PosSession extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'pos_terminal_id',
        'session_no',
        'status',
        'opening_cash_usd',
        'opening_cash_khr',
        'expected_cash_usd',
        'expected_cash_khr',
        'actual_cash_usd',
        'actual_cash_khr',
        'cash_variance_usd',
        'cash_variance_khr',
        'total_sales_usd',
        'total_sales_khr',
        'total_cash_usd',
        'total_cash_khr',
        'total_ebanking_usd',
        'total_ebanking_khr',
        'total_pay_later_usd',
        'total_pay_later_khr',
        'opened_at',
        'closed_at',
        'opened_by',
        'closed_by',
        'cancelled_by',
        'opening_note',
        'closing_note',
        'cancel_reason',
    ];

    protected $casts = [
        'opening_cash_usd' => 'decimal:2',
        'opening_cash_khr' => 'decimal:2',

        'expected_cash_usd' => 'decimal:2',
        'expected_cash_khr' => 'decimal:2',

        'actual_cash_usd' => 'decimal:2',
        'actual_cash_khr' => 'decimal:2',

        'cash_variance_usd' => 'decimal:2',
        'cash_variance_khr' => 'decimal:2',

        'total_sales_usd' => 'decimal:2',
        'total_sales_khr' => 'decimal:2',

        'total_cash_usd' => 'decimal:2',
        'total_cash_khr' => 'decimal:2',

        'total_ebanking_usd' => 'decimal:2',
        'total_ebanking_khr' => 'decimal:2',

        'total_pay_later_usd' => 'decimal:2',
        'total_pay_later_khr' => 'decimal:2',

        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
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

    /** @return BelongsTo<User, $this> */
    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    /** @return BelongsTo<User, $this> */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /** @return BelongsTo<User, $this> */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /** @return HasMany<DiningSession, $this> */
    public function diningSessions(): HasMany
    {
        return $this->hasMany(DiningSession::class);
    }
}
