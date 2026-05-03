<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Menu;
use App\Models\OrderLine;   

class Tax extends Model
{
    protected $guarded = [];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function orderLines()
    {
        return $this->hasMany(OrderLine::class);
    }
}