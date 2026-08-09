<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Agency extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'logo_url',
        'role',          // ✅ नयाँ: role (super_admin, admin, agency)
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ---------------------------
    // सम्बन्ध (Relationships)
    // ---------------------------

    /**
     * Agency का सबै Treks
     */
    public function treks()
    {
        return $this->hasMany(Trek::class);
    }

    /**
     * Agency का सबै Bookings (Treks मार्फत)
     * यसले agencies → treks → bookings सम्बन्ध बनाउँछ
     */
    public function bookings()
    {
        return $this->hasManyThrough(
            Booking::class,   // अन्तिम मोडेल
            Trek::class,      // मध्यवर्ती मोडेल
            'agency_id',      // Trek मा foreign key
            'trek_id',        // Booking मा foreign key
            'id',             // Agency मा local key
            'id'              // Trek मा local key
        );
    }

    // ---------------------------
    // सहायक विधिहरू (Helpers)
    // ---------------------------

    /**
     * के यो एजेन्सी सुपर एडमिन हो?
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * के यो एजेन्सी एडमिन हो (super_admin वा admin)?
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    // यदि तपाईंलाई सामान्य एजेन्सी पहिचान गर्नु छ भने:
    public function isRegularAgency(): bool
    {
        return $this->role === 'agency' || is_null($this->role);
    }
}