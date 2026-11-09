<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RescueFleet extends Model
{
    protected $fillable = [
        'provider_id', 'registration_number', 'type', 'model',
        'max_altitude_ft', 'status', 'pilot_name', 'last_ping_at',
    ];

    protected $casts = [
        'last_ping_at' => 'datetime',
        'max_altitude_ft' => 'integer',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function missions() { return $this->hasMany(RescueMission::class); }
}