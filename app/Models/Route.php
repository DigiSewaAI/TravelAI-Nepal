<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'difficulty',
        'duration_days', 'max_altitude', 'season', 'is_active'
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'max_altitude' => 'integer',
        'is_active' => 'boolean',
    ];

    public function segments()
    {
        return $this->hasMany(RouteSegment::class);
    }

    public function costs()
    {
        return $this->hasMany(RouteCost::class);
    }

    public function plannerRequests()
    {
        return $this->hasMany(PlannerRequest::class);
    }
}