<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read DiningResourceType|null $diningResourceType
 * @property-read DiningSession|null $activeSession
 */
class DiningResource extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
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

    /** @return BelongsTo<DiningResourceType, $this> */
    public function diningResourceType(): BelongsTo
    {
        return $this->belongsTo(DiningResourceType::class);
    }

    /** @return HasMany<ResourceBooking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(ResourceBooking::class);
    }

    /** @return HasMany<DiningSession, $this> */
    public function sessions(): HasMany
    {
        return $this->hasMany(DiningSession::class);
    }

    /** @return HasOne<DiningSession, $this> */
    public function activeSession(): HasOne
    {
        return $this->hasOne(DiningSession::class)
            ->whereIn('status', ['open', 'invoiced', 'partially_paid', 'paid', 'pay_later']);
    }
}
