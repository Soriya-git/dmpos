<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Customer|null $customer
 * @property-read DiningResource|null $diningResource
 * @property-read Collection<int, Order> $orders
 * @property-read Collection<int, Invoice> $invoices
 */
class DiningSession extends Model
{
    protected $guarded = [];

    protected $casts = [
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

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<DiningResource, $this> */
    public function diningResource(): BelongsTo
    {
        return $this->belongsTo(DiningResource::class);
    }

    /** @return BelongsTo<ResourceBooking, $this> */
    public function resourceBooking(): BelongsTo
    {
        return $this->belongsTo(ResourceBooking::class);
    }

    /** @return BelongsTo<MenuPriceList, $this> */
    public function menuPriceList(): BelongsTo
    {
        return $this->belongsTo(MenuPriceList::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** @return HasMany<Invoice, $this> */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
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

    /** @return HasMany<SessionLog, $this> */
    public function logs(): HasMany
    {
        return $this->hasMany(SessionLog::class);
    }

    /** @return BelongsTo<PosSession, $this> */
    public function posSession(): BelongsTo
    {
        return $this->belongsTo(PosSession::class);
    }
}
