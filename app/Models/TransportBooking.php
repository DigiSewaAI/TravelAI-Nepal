<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportBooking extends Model
{
    protected $fillable = [
        'provider_id', 'vehicle_id', 'passenger_name', 'passenger_email',
        'booking_ref', 'route_name', 'travel_date', 'amount', 'status'
    ];

    protected $casts = [
        'travel_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function vehicle()  { return $this->belongsTo(TransportVehicle::class, 'vehicle_id'); }
}