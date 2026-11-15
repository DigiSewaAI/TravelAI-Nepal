<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportBooking extends Model
{
    protected $fillable = [
        'provider_id', 'vehicle_id', 'trip_id',
        'passenger_name', 'passenger_email', 'passenger_count',
        'booking_type', 'pickup_point', 'price_per_unit', 'total_amount',
        'booking_ref', 'booking_number', 'route_name', 'travel_date', 'travel_time',
        'amount', 'status',
        'payment_status', 'payment_method', 'payment_ref', 'payment_proof', 'paid_at', 'payment_model', 'payment_submitted_at',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'amount' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'passenger_count' => 'integer',
        'paid_at' => 'datetime', 'payment_submitted_at' => 'datetime',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function vehicle()  { return $this->belongsTo(TransportVehicle::class, 'vehicle_id'); }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function trip()
    {
        return $this->belongsTo(TransportTrip::class, 'trip_id');
    }
}