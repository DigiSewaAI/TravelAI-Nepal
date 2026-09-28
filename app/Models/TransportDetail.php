<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'transport_type',
        'from_location_id',
        'to_location_id',
        'departure_time',
        'duration_minutes',
        'price_per_person',
        'price_per_vehicle',
        'total_seats',
        'ac_available',
        'private_shared',
        'driver_included',
        'fuel_included',
        'booking_type',
        'cancellation_policy',
        'description',
    ];

    protected $casts = [
        'ac_available' => 'boolean',
        'driver_included' => 'boolean',
        'fuel_included' => 'boolean',
        'duration_minutes' => 'integer',
        'total_seats' => 'integer',
        'price_per_person' => 'decimal:2',
        'price_per_vehicle' => 'decimal:2',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }
}