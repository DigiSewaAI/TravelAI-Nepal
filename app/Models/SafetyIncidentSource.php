<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SafetyIncidentSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id', 'source_id', 'source_url', 'source_title',
        'published_at', 'retrieved_at', 'source_type', 'source_reliability',
        'evidence_text', 'content_hash', 'metadata'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'retrieved_at' => 'datetime',
        'source_reliability' => 'float',
        'metadata' => 'array',
    ];

    public function incident()
    {
        return $this->belongsTo(TravelSafetyIncident::class);
    }

    public function source()
    {
        return $this->belongsTo(SafetySource::class);
    }
}