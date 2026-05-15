<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $guarded = [];

    protected $casts = [
        'cost' => 'decimal:4',
        'minimum_stock_qty' => 'decimal:4',
        'is_stockable' => 'boolean',
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

    /** @return BelongsTo<Unit, $this> */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /** @return BelongsTo<Printer, $this> */
    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }

    /** @return HasMany<BomLine, $this> */
    public function bomLines(): HasMany
    {
        return $this->hasMany(BomLine::class, 'component_item_id');
    }

    /** @return HasMany<Menu, $this> */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    /** @return HasMany<BomHeader, $this> */
    public function outputBomHeaders(): HasMany
    {
        return $this->hasMany(BomHeader::class, 'output_item_id');
    }

    /** @return HasMany<ItemUnitConversion, $this> */
    public function unitConversions(): HasMany
    {
        return $this->hasMany(ItemUnitConversion::class);
    }

    /** @return HasMany<StockBalance, $this> */
    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    /** @return HasMany<StockMovement, $this> */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
