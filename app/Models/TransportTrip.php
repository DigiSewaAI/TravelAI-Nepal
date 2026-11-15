<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportTrip extends Model
{
    protected $fillable = [
        'provider_id',
        'vehicle_id',
        'route_id',
        'departure_date',
        'departure_time',
        'price_per_seat',
        'price_per_vehicle',
        'seats_total',
        'seats_available',
        'driver_name',
        'pickup_point',
        'status',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'price_per_seat' => 'decimal:2',
        'price_per_vehicle' => 'decimal:2',
        'seats_total' => 'integer',
        'seats_available' => 'integer',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function vehicle() { return $this->belongsTo(TransportVehicle::class, 'vehicle_id'); }
    public function route() { return $this->belongsTo(TransportRoute::class, 'route_id'); }
    public function bookings() { return $this->hasMany(TransportBooking::class, 'trip_id'); }
}