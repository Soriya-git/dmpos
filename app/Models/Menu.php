<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read MenuCategory|null $menuCategory
 * @property-read MenuPrice|null $defaultPrice
 */
class Menu extends Model
{
    protected $guarded = [];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_available' => 'boolean',
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

    /** @return BelongsTo<MenuCategory, $this> */
    public function menuCategory(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class);
    }

    /** @return BelongsTo<Tax, $this> */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    /** @return HasMany<MenuPrice, $this> */
    public function prices(): HasMany
    {
        return $this->hasMany(MenuPrice::class);
    }

    /** @return HasOne<MenuPrice, $this> */
    public function defaultPrice(): HasOne
    {
        return $this->hasOne(MenuPrice::class)->where('is_default', true);
    }

    /** @return HasMany<BomHeader, $this> */
    public function bomHeaders(): HasMany
    {
        return $this->hasMany(BomHeader::class);
    }

    /** @return HasOne<BomHeader, $this> */
    public function activeBom(): HasOne
    {
        return $this->hasOne(BomHeader::class)->where('status', 'active');
    }

    /** @return HasMany<OrderLine, $this> */
    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }
}
