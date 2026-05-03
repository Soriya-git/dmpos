<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenTicket extends Model
{
    protected $guarded = [];

    protected $casts = [
        'printed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function diningSession()
    {
        return $this->belongsTo(DiningSession::class);
    }

    public function diningResource()
    {
        return $this->belongsTo(DiningResource::class);
    }

    public function lines()
    {
        return $this->hasMany(KitchenTicketLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}