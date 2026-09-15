<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'features',
        'limits',
        'requires_contact',
    ];

    protected $casts = [
        'features'         => 'array',
        'limits'           => 'array',
        'price_monthly'    => 'decimal:2',
        'price_yearly'     => 'decimal:2',
        'requires_contact' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Contact-only plans (e.g. Enterprise) cannot be activated via public flows.
     */
    public function isContactOnly(): bool
    {
        return (bool) $this->requires_contact;
    }

    /**
     * A plan is "free" only if it is NOT contact-only and both prices are zero.
     */
    public function isFree(): bool
    {
        if ($this->isContactOnly()) {
            return false;
        }

        return ($this->price_monthly ?? 0) == 0 && ($this->price_yearly ?? 0) == 0;
    }
}