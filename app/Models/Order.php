<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Collection<int, OrderLine> $orderLines
 */
class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'pos_open_date' => 'date',
        'sent_to_kitchen_at' => 'datetime',
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

    /** @return BelongsTo<DiningSession, $this> */
    public function diningSession(): BelongsTo
    {
        return $this->belongsTo(DiningSession::class);
    }

    /** @return BelongsTo<MenuPriceList, $this> */
    public function menuPriceList(): BelongsTo
    {
        return $this->belongsTo(MenuPriceList::class);
    }

    /** @return HasMany<OrderLine, $this> */
    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    /** @return HasMany<KitchenTicket, $this> */
    public function kitchenTickets(): HasMany
    {
        return $this->hasMany(KitchenTicket::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
