<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class TravelSafetyIncident extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'title', 'slug', 'incident_type', 'description', 'severity', 'status',
        'latitude', 'longitude', 'location_name', 'district', 'province',
        'affected_radius', 'started_at', 'reported_at', 'last_verified_at',
        'expires_at', 'confidence_score', 'official_confirmation',
        'travel_impact', 'recommended_action', 'raw_source_reference'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'affected_radius' => 'integer',
        'started_at' => 'datetime',
        'reported_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'confidence_score' => 'float',
        'official_confirmation' => 'boolean',
        'raw_source_reference' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title . '-' . uniqid());
            }
        });
    }

    /**
     * Sources (many-to-many with explicit incident_id)
     */
    public function sources()
    {
        return $this->belongsToMany(SafetySource::class, 'safety_incident_sources', 'incident_id', 'source_id')
                    ->withPivot('source_url', 'source_title', 'published_at', 'retrieved_at', 'source_type', 'source_reliability', 'evidence_text', 'content_hash', 'metadata')
                    ->withTimestamps();
    }

    /**
     * Affected waypoints (polymorphic many-to-many)
     */
    public function waypoints()
    {
        return $this->morphedByMany(Waypoint::class, 'affectable', 'incident_affectables', 'incident_id', 'affectable_id')
                    ->withPivot('distance', 'match_type', 'confidence', 'metadata');
    }

    /**
     * Affected routes (polymorphic many-to-many)
     */
    public function routes()
    {
        return $this->morphedByMany(Route::class, 'affectable', 'incident_affectables', 'incident_id', 'affectable_id')
                    ->withPivot('distance', 'match_type', 'confidence', 'metadata');
    }

    /**
     * Affected treks (polymorphic many-to-many)
     */
    public function treks()
    {
        return $this->morphedByMany(Trek::class, 'affectable', 'incident_affectables', 'incident_id', 'affectable_id')
                    ->withPivot('distance', 'match_type', 'confidence', 'metadata');
    }

    /**
     * Affected locations (polymorphic many-to-many)
     */
    public function locations()
    {
        return $this->morphedByMany(Location::class, 'affectable', 'incident_affectables', 'incident_id', 'affectable_id')
                    ->withPivot('distance', 'match_type', 'confidence', 'metadata');
    }

    /**
     * Audit logs for this incident
     */
    public function auditLogs()
    {
        return $this->hasMany(SafetyAuditLog::class);
    }

    /**
     * Traveler alerts generated from this incident
     */
    public function travelerAlerts()
    {
        return $this->hasMany(TravelerSafetyAlert::class);
    }
}