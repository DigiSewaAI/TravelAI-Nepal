<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyAuditLog extends Model
{
    protected $fillable = [
        'incident_id', 'action', 'old_values', 'new_values',
        'user_id', 'ip_address', 'user_agent', 'reason'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function incident()
    {
        return $this->belongsTo(TravelSafetyIncident::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}