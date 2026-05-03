<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\DiningResource;
use App\Models\DiningSession;
use App\Models\User;

class ResourceBooking extends Model
{
    protected $guarded = [];

    protected $casts = [
        'booking_start' => 'datetime',
        'booking_end' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function diningResource()
    {
        return $this->belongsTo(DiningResource::class);
    }

    public function diningSession()
    {
        return $this->hasOne(DiningSession::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}