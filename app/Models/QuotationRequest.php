<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'traveler_id',
        'traveler_name',
        'traveler_email',
        'traveler_phone',
        'provider_id',
        'planner_result_id',
        'itinerary_data',
        'traveler_input',
        'message',
        'status',
        'quotation_data',
        'quotation_text',
        // ✅ NEW FIELDS (added)
        'quotation_final',
        'quotation_status',
        'edited_at',
        'sent_at',
        'edited_by',
    ];

    protected $casts = [
        'itinerary_data' => 'array',
        'traveler_input' => 'array',
        'quotation_data' => 'array',
        'quotation_final' => 'array',
        'sent_at' => 'datetime',
        'edited_at' => 'datetime',
    ];

    // Relationships
    public function traveler()
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function plannerResult()
    {
        return $this->belongsTo(PlannerResult::class);
    }

    // Existing status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    // ✅ NEW helper methods for quotation lifecycle
    public function isQuotationSent(): bool
    {
        return $this->quotation_status === 'sent';
    }

    public function isQuotationEditable(): bool
    {
        return $this->quotation_status !== 'sent';
    }
}