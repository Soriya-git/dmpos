<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;
use App\Models\Branch;
use App\Models\DiningSession;
use App\Models\OrderLine;
use App\Models\KitchenTicket;
use App\Models\User;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sent_to_kitchen_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function diningSession()
    {
        return $this->belongsTo(DiningSession::class);
    }

    public function orderLines()
    {
        return $this->hasMany(OrderLine::class);
    }

    public function kitchenTickets()
    {
        return $this->hasMany(KitchenTicket::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}