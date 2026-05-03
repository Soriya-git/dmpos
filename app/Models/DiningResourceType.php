<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiningResourceType extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function diningResources()
    {
        return $this->hasMany(DiningResource::class);
    }
}
