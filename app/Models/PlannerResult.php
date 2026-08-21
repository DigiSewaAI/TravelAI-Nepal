<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlannerResult extends Model
{
    protected $fillable = [
        'request_id', 'raw_ai_response', 'model', 'model_version',
        'prompt_version', 'route_snapshot', 'validation_status',
        'fallback_used', 'validation_errors'
    ];

    protected $casts = [
        'raw_ai_response' => 'array',
        'route_snapshot' => 'array',
        'validation_errors' => 'array',
        'fallback_used' => 'boolean',
    ];

    public function request()
    {
        return $this->belongsTo(PlannerRequest::class);
    }

    // ✅ यहाँ result_id स्पष्ट रूपमा दिइयो
    public function days()
    {
        return $this->hasMany(ItineraryDay::class, 'result_id');
    }
}