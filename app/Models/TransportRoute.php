<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportRoute extends Model
{
    protected $fillable = [
        'provider_id',
        'from_location_id',
        'to_location_id',
        'name',
        'duration_minutes',
        'distance_km',
        'is_active',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'distance_km' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function fromLocation() { return $this->belongsTo(Location::class, 'from_location_id'); }
    public function toLocation() { return $this->belongsTo(Location::class, 'to_location_id'); }
    public function trips() { return $this->hasMany(TransportTrip::class, 'route_id'); }
}