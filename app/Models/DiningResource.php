<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Company;
use App\Models\Branch;
use App\Models\DiningResourceType;
use App\Models\ResourceBooking;
use App\Models\DiningSession;



class DiningResource extends Model
{
    protected $guarded = [];

    protected $casts = [
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

    public function diningResourceType()
    {
        return $this->belongsTo(DiningResourceType::class);
    }

    public function bookings()
    {
        return $this->hasMany(ResourceBooking::class);
    }

    public function sessions()
    {
        return $this->hasMany(DiningSession::class);
    }

    public function activeSession()
    {
        return $this->hasOne(DiningSession::class)
            ->whereIn('status', ['open', 'invoiced', 'partially_paid', 'paid', 'pay_later']);
    }
}
