<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Waypoint extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'type', 'latitude', 'longitude',
        'altitude', 'description', 'metadata'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'altitude' => 'integer',
        'metadata' => 'array',
    ];

    // Relationships
    public function fromSegments()
    {
        return $this->hasMany(RouteSegment::class, 'from_waypoint_id');
    }

    public function toSegments()
    {
        return $this->hasMany(RouteSegment::class, 'to_waypoint_id');
    }
    public function qrScans()
{
    return $this->hasMany(QrScan::class);
}
}