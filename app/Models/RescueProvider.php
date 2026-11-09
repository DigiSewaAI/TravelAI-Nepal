<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RescueProvider extends Model
{
    protected $table = 'rescue_providers';

    // ✅ १. यहाँ नयाँ फिल्डहरू थप्नुहोस्
    protected $fillable = [
        'provider_id',
        'aviation_license_number',
        'medical_cert_number',
        'insurance_policy_number',
        'coverage_districts',
        'coverage_radius_km',
        'base_rate_npr',
        'per_km_rate_npr',
        'per_hour_rate_npr',
        'night_surcharge_pct',
        'high_altitude_surcharge_pct',
        'max_altitude_ft',
        'fleet_size',
        'is_24_7',
        'is_available',
        'staff_members',       // ✅ नयाँ
        'insurance_partners',  // ✅ नयाँ
        'documents',           // ✅ नयाँ
    ];

    // ✅ २. Casts मा Array थप्नुहोस्
    protected $casts = [
        'coverage_districts'          => 'array',
        'staff_members'               => 'array', // ✅ नयाँ
        'insurance_partners'          => 'array', // ✅ नयाँ
        'documents'                   => 'array', // ✅ नयाँ
        'coverage_radius_km'          => 'integer',
        'base_rate_npr'               => 'decimal:2',
        'per_km_rate_npr'             => 'decimal:2',
        'per_hour_rate_npr'           => 'decimal:2',
        'night_surcharge_pct'         => 'integer',
        'high_altitude_surcharge_pct' => 'integer',
        'max_altitude_ft'             => 'integer',
        'fleet_size'                  => 'integer',
        'is_24_7'                     => 'boolean',
        'is_available'                => 'boolean',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}