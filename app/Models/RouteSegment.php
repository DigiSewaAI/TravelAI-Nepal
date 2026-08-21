<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteSegment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'route_id', 'from_waypoint_id', 'to_waypoint_id',
        'sequence', 'distance_km', 'estimated_time_hours',
        'elevation_gain_m', 'elevation_loss_m'
    ];

    protected $casts = [
        'distance_km' => 'decimal:2',
        'estimated_time_hours' => 'decimal:1',
        'elevation_gain_m' => 'integer',
        'elevation_loss_m' => 'integer',
        'sequence' => 'integer',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function fromWaypoint()
    {
        return $this->belongsTo(Waypoint::class, 'from_waypoint_id');
    }

    public function toWaypoint()
    {
        return $this->belongsTo(Waypoint::class, 'to_waypoint_id');
    }
}