<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;
use App\Models\Branch;
use App\Models\StockAdjustment;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Menu;
use App\Models\BomHeader;

class StockAdjustmentLine extends Model
{
    protected $guarded = [];

    protected $casts = [
        'system_quantity' => 'decimal:4',
        'adjusted_quantity' => 'decimal:4',
        'difference_quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
    ];

    public function stockAdjustment()
    {
        return $this->belongsTo(StockAdjustment::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function bomHeader()
    {
        return $this->belongsTo(BomHeader::class);
    }
}