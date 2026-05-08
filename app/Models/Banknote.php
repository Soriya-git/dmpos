<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banknote extends Model
{
    protected $fillable = [
        'currency_type',
        'denomination',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'denomination' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
