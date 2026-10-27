<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportVehicle extends Model
{
    protected $fillable = ['provider_id', 'vehicle_number', 'vehicle_type', 'capacity', 'driver_name', 'route_name', 'status'];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function bookings() { return $this->hasMany(TransportBooking::class, 'vehicle_id'); }
}