<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
