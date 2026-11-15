<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportBooking extends Model
{
    protected $fillable = [
        'provider_id', 'vehicle_id', 'passenger_name', 'passenger_email',
        'booking_ref', 'booking_number', 'route_name', 'travel_date', 'travel_time',
        'amount', 'status',
        'payment_status', 'payment_method', 'payment_ref', 'payment_proof', 'paid_at',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function vehicle()  { return $this->belongsTo(TransportVehicle::class, 'vehicle_id'); }
        public function service()
    {
        return $this->belongsTo(Service::class);
    }
}