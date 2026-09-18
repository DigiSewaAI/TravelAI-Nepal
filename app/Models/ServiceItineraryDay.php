<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceItineraryDay extends Model
{
    protected $fillable = [
        'service_id',
        'day_number',
        'title',
        'description',
        'start_waypoint_id',
        'end_waypoint_id',
        'overnight_waypoint_id',
        'distance_km',
        'estimated_time_hours',
        'elevation_gain_m',
        'elevation_loss_m',
        'altitude_m',
        'meals_included',
        'accommodation',
    ];

    protected $casts = [
        'day_number' => 'integer',
        'distance_km' => 'decimal:2',
        'estimated_time_hours' => 'decimal:1',
        'elevation_gain_m' => 'integer',
        'elevation_loss_m' => 'integer',
        'altitude_m' => 'integer',
        'meals_included' => 'array',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function items()
    {
        return $this->hasMany(ServiceItineraryItem::class, 'day_id')
            ->orderBy('sort_order');
    }

    public function media()
    {
        return $this->hasMany(ServiceItineraryDayMedia::class, 'day_id')
            ->orderBy('sort_order');
    }

    public function startWaypoint()
    {
        return $this->belongsTo(Waypoint::class, 'start_waypoint_id');
    }

    public function endWaypoint()
    {
        return $this->belongsTo(Waypoint::class, 'end_waypoint_id');
    }

    public function overnightWaypoint()
    {
        return $this->belongsTo(Waypoint::class, 'overnight_waypoint_id');
    }
}