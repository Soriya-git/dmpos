<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionLog extends Model
{
    protected $guarded = [];

    protected $casts = [
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

    public function diningSession()
    {
        return $this->belongsTo(DiningSession::class);
    }

    public function sourceResource()
    {
        return $this->belongsTo(DiningResource::class, 'from_resource_id');
    }

    public function targetResource()
    {
        return $this->belongsTo(DiningResource::class, 'to_resource_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

}