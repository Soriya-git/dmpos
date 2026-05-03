<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity_before' => 'decimal:4',
        'quantity_after' => 'decimal:4',
        'quantity_changed' => 'decimal:4',
        'payload' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockMovement()
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
