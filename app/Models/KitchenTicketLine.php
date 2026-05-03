<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenTicketLine extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function kitchenTicket()
    {
        return $this->belongsTo(KitchenTicket::class);
    }

    public function orderLine()
    {
        return $this->belongsTo(OrderLine::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
