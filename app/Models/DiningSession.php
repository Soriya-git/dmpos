<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiningSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function posTerminal()
    {
        return $this->belongsTo(PosTerminal::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function diningResource()
    {
        return $this->belongsTo(DiningResource::class);
    }

    public function resourceBooking()
    {
        return $this->belongsTo(ResourceBooking::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function opener()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function logs()
    {
        return $this->hasMany(SessionLog::class);
    }

    public function posSession()
    {
        return $this->belongsTo(PosSession::class);
    }
}
