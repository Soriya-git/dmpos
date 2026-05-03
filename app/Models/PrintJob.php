<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Printer;
use App\Models\PrintTemplate;
use App\Models\User;    

class PrintJob extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
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

    public function printer()
    {
        return $this->belongsTo(Printer::class);
    }

    public function printTemplate()
    {
        return $this->belongsTo(PrintTemplate::class);
    }

    public function printedBy()
    {
        return $this->belongsTo(User::class, 'printed_by');
    }
}