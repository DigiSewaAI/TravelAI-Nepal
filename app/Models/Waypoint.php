<?php

namespace App\Models;

use App\Models\Traits\HasSafetyStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Waypoint extends Model
{
    use SoftDeletes, HasSafetyStatus;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'latitude',
        'longitude',
        'altitude',
        'description',
        'metadata',
        'safety_status',
        'safety_updated_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'altitude' => 'integer',
        'metadata' => 'array',
        'safety_updated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name . '-' . uniqid());
            }
        });
    }

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