<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Company;
use App\Models\Branch;
use App\Models\MenuCategory;
use App\Models\Tax;
use App\Models\MenuPrice;
use App\Models\BomHeader;
use App\Models\OrderLine;

class Menu extends Model
{
    protected $guarded = [];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function menuCategory()
    {
        return $this->belongsTo(MenuCategory::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function prices()
    {
        return $this->hasMany(MenuPrice::class);
    }

    public function defaultPrice()
    {
        return $this->hasOne(MenuPrice::class)->where('is_default', true);
    }

    public function bomHeaders()
    {
        return $this->hasMany(BomHeader::class);
    }

    public function activeBom()
    {
        return $this->hasOne(BomHeader::class)->where('status', 'active');
    }

    public function orderLines()
    {
        return $this->hasMany(OrderLine::class);
    }
}