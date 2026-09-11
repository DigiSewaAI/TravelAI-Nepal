<?php

namespace App\Models;

use App\Models\Traits\HasSafetyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory, HasSafetyStatus;

    protected $fillable = [
        'country',
        'state',
        'city',
        'latitude',
        'longitude',
        'is_habitable',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_habitable' => 'boolean',
    ];

    // Relationships

    /**
     * ✅ ADDED (Phase 4A — A-6)
     * Reverse relationship: Location has many Waypoints.
     * Used in Phase 4F service matching hierarchy.
     */
    public function waypoints()
    {
        return $this->hasMany(Waypoint::class);
    }
}