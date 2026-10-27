<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndividualBooking extends Model
{
    protected $fillable = [
        'provider_id', 'service_type', 'client_name', 'title', 'pax',
        'start_date', 'duration', 'amount', 'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
}