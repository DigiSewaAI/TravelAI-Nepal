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
        'contact_email',
        'contact_phone',
        'address',
        'verification_status',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function types()
    {
        return $this->belongsToMany(ProviderType::class, 'provider_provider_type');
    }

    public function staff()
    {
        return $this->hasMany(ProviderStaff::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function documents()
    {
        return $this->hasMany(VerificationDocument::class);
    }

    // Future services will be added later
}