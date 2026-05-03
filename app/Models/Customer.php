<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Company;
use App\Models\Branch;
use App\Models\CustomerGroup;
use App\Models\ResourceBooking;
use App\Models\DiningSession;
use App\Models\Invoice;

class Customer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_general_customer' => 'boolean',
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

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function resourceBookings()
    {
        return $this->hasMany(ResourceBooking::class);
    }

    public function diningSessions()
    {
        return $this->hasMany(DiningSession::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}