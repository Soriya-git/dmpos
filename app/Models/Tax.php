<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
