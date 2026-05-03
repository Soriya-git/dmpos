<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $customer_name
 * @property string|null $customer_phone
 * @property string|null $mobile
 * @property string|null $phone
 */
class Customer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_general_customer' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected function customerName(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->name);
    }

    protected function customerPhone(): Attribute
    {
        return Attribute::get(fn (): string => $this->phone_number);
    }

    protected function mobile(): Attribute
    {
        return Attribute::get(fn (): string => $this->phone_number);
    }

    protected function phone(): Attribute
    {
        return Attribute::get(fn (): string => $this->phone_number);
    }

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

    /** @return BelongsTo<CustomerGroup, $this> */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    /** @return HasMany<ResourceBooking, $this> */
    public function resourceBookings(): HasMany
    {
        return $this->hasMany(ResourceBooking::class);
    }

    /** @return HasMany<DiningSession, $this> */
    public function diningSessions(): HasMany
    {
        return $this->hasMany(DiningSession::class);
    }

    /** @return HasMany<Invoice, $this> */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
