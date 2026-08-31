<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelerSafetyAlert extends Model
{
    protected $fillable = [
        'user_id', 'incident_id', 'affectable_type', 'affectable_id',
        'alert_type', 'severity', 'sent_at', 'read_at',
        'delivery_channel', 'message', 'metadata'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function incident()
    {
        return $this->belongsTo(TravelSafetyIncident::class);
    }

    public function affectable()
    {
        return $this->morphTo();
    }
}