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
        'website',
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
     * FIX-14: Canonical staff relationship.
     * Returns ProviderStaff memberships (join table).
     * Do NOT use User->provider_id — that column does not exist.
     */
    public function staff()
    {
        return $this->hasMany(ProviderStaff::class, 'provider_id');
    }

    /**
     * FIX-14: Alias returning User models (via provider_staff join).
     */
    public function staffUsers()
    {
        return $this->hasManyThrough(
            User::class,
            ProviderStaff::class,
            'provider_id',  // FK on provider_staff → providers
            'id',           // FK on users (matched by provider_staff.user_id)
            'id',           // local key on providers
            'user_id'       // local key on provider_staff
        );
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

    /**
     * Check if the provider's active subscription plan includes a feature.
     * Centralized entitlement check (FIX-05 Phase 3).
     *
     * @param string $feature  Canonical slug: advanced_dashboard,
     *                         full_analytics, white_label,
     *                         custom_logo, priority_support
     */
    public function hasFeature(string $feature): bool
    {
        $subscription = $this->activeSubscription()->first();

        if (!$subscription || !$subscription->isActive()) {
            return false;
        }

        $plan = $subscription->plan;

        if (!$plan) {
            return false;
        }

        $features = $plan->features;

        if (!is_array($features)) {
            return false;
        }

        return in_array($feature, $features, true);
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
        /**
     * FIX-15: Get the maximum number of active service listings allowed
     * for the provider's current plan.
     * Returns -1 for unlimited (Enterprise).
     *
     * Mirrors getMaxStaffAttribute() semantics. Callers must convert -1
     * to PHP_INT_MAX for boundary comparison.
     */
    public function getMaxListingsAttribute(): int
    {
        $plan = $this->getActivePlanAttribute();

        if (!$plan) {
            return 3; // Free plan default
        }

        if (isset($plan->limits['max_listings'])) {
            return (int) $plan->limits['max_listings'];
        }

        // Fallback by plan slug
        return match ($plan->slug) {
            'free' => 3,
            'professional' => 20,
            'business' => 100,
            'enterprise' => -1,
            default => 3,
        };
    }
    public function styles()
{
    return $this->hasMany(ProviderStyle::class, 'provider_id');
}
public function supportsStyle(string $styleSlug): bool
{
    return $this->styles()->where('style_slug', $styleSlug)->exists();
}
}