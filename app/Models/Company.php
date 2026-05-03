<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Branch;
use App\Models\Company;

class Company extends Model
{
    protected $guarded = [];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
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

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function printers()
    {
        return $this->hasMany(Printer::class);
    }
}
