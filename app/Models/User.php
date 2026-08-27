<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// 🔥 Imports
use App\Models\Provider;
use App\Models\Review;
use App\Models\Booking;

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
        'provider_id', // 👈 Staff/Provider Owner को लागि
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
     * Get the provider this user belongs to (for staff users).
     * Uses provider_id field on the users table.
     */
    public function staffProvider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    /**
     * Alias for staffProvider() – returns the provider this user is associated with.
     * Works for both staff and provider_owner (via provider_id).
     */
    public function associatedProvider()
    {
        if ($this->provider_id) {
            return $this->belongsTo(Provider::class, 'provider_id');
        }
        return $this->hasOne(Provider::class, 'user_id');
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
        // Super Admin can access everything
        if ($this->isSuperAdmin()) {
            return Provider::pluck('id')->toArray();
        }

        $ids = [];

        // Provider Owner: their own providers
        if ($this->isProviderOwner()) {
            $ids = $this->providers()->pluck('id')->toArray();
        }

        // Staff: the provider they belong to
        if ($this->isStaff() && $this->provider_id) {
            $ids[] = $this->provider_id;
        }

        return array_unique($ids);
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

        if ($this->isStaff() && $this->provider_id) {
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