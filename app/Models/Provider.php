<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'logo_url',
        'cover_image',
        'contact_email',
        'contact_phone',
        'address',
        'verification_status',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function types()
    {
        return $this->belongsToMany(ProviderType::class, 'provider_provider_type');
    }

    /**
     * Get all staff members (users with provider_id = this provider)
     * This replaces the old ProviderStaff relationship.
     */
    public function staff()
    {
        return $this->hasMany(User::class, 'provider_id');
    }

    /**
     * Alias for staff() for clarity
     */
    public function staffUsers()
    {
        return $this->staff();
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function bookings()
    {
        return $this->hasManyThrough(Booking::class, Service::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the latest active subscription for this provider.
     */
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
                    ->where('status', 'active')
                    ->latest('id');
    }

    /**
     * Get the active subscription plan (convenience method)
     */
    public function getActivePlanAttribute()
    {
        $subscription = $this->activeSubscription()->first();
        return $subscription ? $subscription->plan : null;
    }

    public function documents()
    {
        return $this->hasMany(VerificationDocument::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // =====================================================
    // HELPERS
    // =====================================================

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    /**
     * Get the maximum number of staff allowed based on the active plan.
     * Returns -1 for unlimited.
     */
    public function getMaxStaffAttribute(): int
    {
        $plan = $this->getActivePlanAttribute();

        if (!$plan) {
            return 1; // Free plan default
        }

        if (isset($plan->limits['max_staff'])) {
            return (int) $plan->limits['max_staff'];
        }

        // Fallback by plan slug
        return match ($plan->slug) {
            'free' => 1,
            'professional' => 5,
            'business' => 20,
            'enterprise' => -1,
            default => 1,
        };
    }
}