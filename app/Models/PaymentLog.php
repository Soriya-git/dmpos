<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;

class PaymentLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount_before' => 'decimal:2',
        'amount_after' => 'decimal:2',
        'amount_changed' => 'decimal:2',
        'exchange_rate_snapshot' => 'decimal:4',
        'payload' => 'array',
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

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}