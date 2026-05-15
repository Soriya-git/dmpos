<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomLine extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:4',
        'wastage_percent' => 'decimal:4',
        'estimated_cost' => 'decimal:4',
    ];

    public function bomHeader()
    {
        return $this->belongsTo(BomHeader::class);
    }

    public function componentItem()
    {
        return $this->belongsTo(Item::class, 'component_item_id');
    }

    public function item()
    {
        return $this->componentItem();
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
