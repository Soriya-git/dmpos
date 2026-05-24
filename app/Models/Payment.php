<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'change_usd_amount' => 'decimal:2',
        'change_khr_amount' => 'decimal:2',
        'exchange_rate_snapshot' => 'decimal:4',
        'amount_usd_equivalent' => 'decimal:2',
        'amount_khr_equivalent' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function membershipCardTransactions()
    {
        return $this->hasMany(MembershipCardTransaction::class);
    }
}
