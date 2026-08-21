<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItineraryDay extends Model
{
    protected $fillable = [
        'result_id', 'day_number', 'title', 'description',
        'overnight_waypoint_id', 'distance_km', 'estimated_time_hours', 'altitude_m'
    ];

    protected $casts = [
        'day_number' => 'integer',
        'distance_km' => 'decimal:2',
        'estimated_time_hours' => 'decimal:1',
        'altitude_m' => 'integer',
    ];

    public function result()
    {
        return $this->belongsTo(PlannerResult::class, 'result_id'); // ✅ explicit
    }

    public function overnightWaypoint()
    {
        return $this->belongsTo(Waypoint::class, 'overnight_waypoint_id');
    }

    // ✅ items relationship (foreign key: day_id)
    public function items()
    {
        return $this->hasMany(ItineraryItem::class, 'day_id');
    }
}