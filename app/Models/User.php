<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// 🔥 Imports
use App\Models\Provider;
use App\Models\Review;
use App\Models\Booking;
use App\Models\ProviderStaff;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

        protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        // ✅ New Fields (Phase 1)
        'passport_public_id',
        'passport_privacy',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the provider owned by this user (for provider_owner role).
     * This is a one-to-one relationship.
     */
    public function provider()
    {
        return $this->hasOne(Provider::class, 'user_id');
    }

        /**
     * FIX-14: Get the provider this user is staff at (via provider_staff).
     * Canonical relationship — uses provider_staff join table.
     */
    public function staffProvider()
    {
        return $this->hasOneThrough(
            Provider::class,
            ProviderStaff::class,
            'user_id',      // FK on provider_staff → users
            'id',           // FK on providers (via provider_staff.provider_id)
            'id',           // local key on users
            'provider_id'   // local key on provider_staff
        );
    }

    /**
     * FIX-14: All staff memberships for this user (may be multiple).
     */
    public function staffMemberships()
    {
        return $this->hasMany(ProviderStaff::class, 'user_id');
    }

        /**
     * FIX-14: Returns the provider this user is associated with.
     * - provider_owner: their owned provider
     * - staff: their staff provider (via provider_staff)
     * - else: null
     * Returns a Provider instance (not a Relation) for direct use.
     */
    public function associatedProvider()
    {
        if ($this->isProviderOwner()) {
            return $this->provider;
        }
        if ($this->isStaff()) {
            return $this->staffProvider;
        }
        return null;
    }

    /**
     * Get all providers owned by this user (for super_admin or multiple providers).
     */
    public function providers()
    {
        return $this->hasMany(Provider::class, 'user_id');
    }

    /**
     * Get bookings made by this user as a traveler.
     */
    public function travelerBookings()
    {
        return $this->hasMany(Booking::class, 'traveler_id');
    }

    /**
     * Get reviews written by this user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // =====================================================
    // ✅ NEW RELATIONSHIPS (Phase 1)
    // =====================================================

    /**
     * Get the achievements earned by this user.
     */
    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
                    ->withPivot('earned_at', 'metadata')
                    ->withTimestamps();
    }

    // =====================================================
    // ROLE HELPERS
    // =====================================================

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isProviderOwner(): bool
    {
        return $this->role === 'provider_owner';
    }

    public function isTraveler(): bool
    {
        return $this->role === 'traveler';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff' || $this->role === 'manager';
    }

    public function isAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    // =====================================================
    // PROVIDER ACCESS HELPERS
    // =====================================================

    /**
     * Get all provider IDs this user can access.
     * Super Admin: all providers
     * Provider Owner: their own provider(s)
     * Staff: the provider they belong to (via provider_id)
     */
        public function accessibleProviderIds(): array
    {
        if ($this->isSuperAdmin()) {
            return Provider::pluck('id')->toArray();
        }

        $ids = [];

        if ($this->isProviderOwner()) {
            $ids = $this->providers()->pluck('id')->toArray();
        }

        // FIX-14: Staff membership via provider_staff (canonical)
        if ($this->isStaff()) {
            $staffProviderIds = ProviderStaff::where('user_id', $this->id)
                ->pluck('provider_id')
                ->toArray();
            $ids = array_merge($ids, $staffProviderIds);
        }

        return array_values(array_unique($ids));
    }

    /**
     * Get the provider this user owns or belongs to.
     * For provider_owner: returns their first owned provider.
     * For staff: returns the provider they belong to.
     * For super_admin: returns null (use providers() instead).
     */
        public function getCurrentProvider()
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        if ($this->isProviderOwner()) {
            return $this->provider;
        }

        // FIX-14: Staff resolves via canonical provider_staff membership
        if ($this->isStaff()) {
            return $this->staffProvider;
        }

        return null;
    }

    /**
     * Check if this user has access to a specific provider.
     */
    public function canAccessProvider(int $providerId): bool
    {
        return in_array($providerId, $this->accessibleProviderIds());
    }

    // =====================================================
    // ADDITIONAL HELPERS
    // =====================================================

    /**
     * Get the full name or a fallback.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? $this->email ?? 'Guest';
    }

    /**
     * Get the user's role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'provider_owner' => 'Provider Owner',
            'staff' => 'Staff',
            'manager' => 'Manager',
            'traveler' => 'Traveler',
            default => 'User',
        };
    }

    /**
     * Get the provider owned by this user (for provider_owner role).
     * This is an alias for the provider() relationship.
     */
    public function ownProvider()
    {
        return $this->provider;
    }
}