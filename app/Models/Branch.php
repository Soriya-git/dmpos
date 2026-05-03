<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $guarded = [];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function posTerminals()
    {
        return $this->hasMany(PosTerminal::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function diningResources()
    {
        return $this->hasMany(DiningResource::class);
    }

    public function diningSessions()
    {
        return $this->hasMany(DiningSession::class);
    }

    public function resourceBookings()
    {
        return $this->hasMany(ResourceBooking::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function printers()
    {
        return $this->hasMany(Printer::class);
    }
}