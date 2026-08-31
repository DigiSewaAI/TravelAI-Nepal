<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SafetySource extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name', 'type', 'base_url', 'feed_url', 'source_category',
        'reliability_score', 'enabled', 'fetch_interval', 'parser_type',
        'last_checked_at', 'last_success_at', 'last_error', 'metadata'
    ];

    protected $casts = [
        'reliability_score' => 'float',
        'enabled' => 'boolean',
        'fetch_interval' => 'integer',
        'last_checked_at' => 'datetime',
        'last_success_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function incidentSources()
    {
        return $this->hasMany(SafetyIncidentSource::class);
    }
}