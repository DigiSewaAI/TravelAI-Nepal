<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RescueMission extends Model
{
    protected $fillable = [
        'mission_number', 'sos_alert_id', 'provider_id', 'rescue_fleet_id',
        'status', 'pickup_lat', 'pickup_lng', 'dropoff_lat', 'dropoff_lng',
        'cost_estimate', 'final_cost', 'dispatched_at', 'accepted_at',
        'en_route_at', 'on_scene_at', 'completed_at',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'accepted_at'   => 'datetime',
        'en_route_at'   => 'datetime',
        'on_scene_at'   => 'datetime',
        'completed_at'  => 'datetime',
        'cost_estimate' => 'decimal:2',
        'final_cost'    => 'decimal:2',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function sos() { return $this->belongsTo(SosAlert::class, 'sos_alert_id'); }
    public function fleet() { return $this->belongsTo(RescueFleet::class, 'rescue_fleet_id'); }
    public function updates() { return $this->hasMany(RescueMissionUpdate::class); }
}