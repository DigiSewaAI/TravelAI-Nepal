<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlannerResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'raw_ai_response',
        'model',
        'model_version',
        'prompt_version',
        'route_snapshot',
        'validation_status',
        'fallback_used',
        'validation_errors',
    ];

    protected $casts = [
        'raw_ai_response' => 'array',
        'route_snapshot' => 'array',
        'validation_errors' => 'array',
        'fallback_used' => 'boolean',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the planner request that owns this result.
     */
    public function plannerRequest()
    {
        return $this->belongsTo(PlannerRequest::class, 'request_id');
    }

    /**
     * Get the itinerary days for this result.
     */
    public function days()
    {
        return $this->hasMany(ItineraryDay::class, 'result_id');
    }
}