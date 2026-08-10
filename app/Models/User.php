<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ===== Relationships =====
    public function providers()
    {
        return $this->hasMany(Provider::class, 'user_id');
    }

    public function staff()
    {
        return $this->hasMany(ProviderStaff::class);
    }

    // ===== Helper methods =====
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

    /**
     * Phase 2: Get IDs of providers this user can manage.
     * Super admin gets all, owners get their own, staff gets assigned.
     */
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