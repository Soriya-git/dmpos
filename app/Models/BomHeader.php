<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomHeader extends Model
{
    protected $guarded = [];

    protected $casts = [
        'output_quantity' => 'decimal:4',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function lines()
    {
        return $this->hasMany(BomLine::class);
    }
}
