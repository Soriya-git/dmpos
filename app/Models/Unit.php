<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $guarded = [];

    protected $casts = [
        'base_quantity' => 'decimal:6',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<Unit, $this> */
    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /** @return HasMany<Unit, $this> */
    public function derivedUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    /** @return HasMany<Item, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /** @return HasMany<BomLine, $this> */
    public function bomLines(): HasMany
    {
        return $this->hasMany(BomLine::class);
    }

    /** @return HasMany<ItemUnitConversion, $this> */
    public function conversionsFrom(): HasMany
    {
        return $this->hasMany(ItemUnitConversion::class, 'from_unit_id');
    }

    /** @return HasMany<ItemUnitConversion, $this> */
    public function conversionsTo(): HasMany
    {
        return $this->hasMany(ItemUnitConversion::class, 'to_unit_id');
    }
}
