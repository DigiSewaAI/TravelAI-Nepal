<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    // Relationships
    public function providers()
    {
        return $this->hasMany(Provider::class, 'user_id');
    }

    public function staff()
    {
        return $this->hasMany(ProviderStaff::class);
    }

    public function travelerBookings()
    {
        return $this->hasMany(Booking::class, 'traveler_id');
    }

    // Helper methods
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

    public function accessibleProviderIds(): array
    {
        if ($this->isSuperAdmin()) {
            return Provider::pluck('id')->toArray();
        }

        $ids = $this->providers()->pluck('id')->toArray();

        if ($this->role === 'staff' || $this->role === 'manager') {
            $staffProviders = $this->staff()->pluck('provider_id')->toArray();
            $ids = array_merge($ids, $staffProviders);
        }

        return array_unique($ids);
    }
}