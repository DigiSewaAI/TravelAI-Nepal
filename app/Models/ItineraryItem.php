<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItineraryItem extends Model
{
    protected $fillable = [
        'day_id', 'title', 'description', 'time_of_day',
        'cost', 'currency', 'pricing_source', 'pricing_snapshot',
        'service_id', 'is_optional', 'metadata'
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'pricing_snapshot' => 'array',
        'metadata' => 'array',
        'is_optional' => 'boolean',
    ];

    public function day()
    {
        return $this->belongsTo(ItineraryDay::class, 'day_id'); // ✅ explicit
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}