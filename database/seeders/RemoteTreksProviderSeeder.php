<?php

namespace Database\Seeders;

class RemoteTreksProviderSeeder extends BaseProviderSeeder
{
    protected function getProviderEmail(): string
    {
        return 'remote-providers@travelai.com';
    }

    protected function getProviderName(): string
    {
        return 'Remote Treks Provider System';
    }

    protected function getLocationData(): array
    {
        return [
            // ============================================================
            // EXISTING LOCATIONS
            // ============================================================
            'Simikot' => ['lat' => 29.9789, 'lng' => 82.0123, 'alt' => 2910],
            'Rara Lake' => ['lat' => 29.3789, 'lng' => 82.3891, 'alt' => 2990],
            'Jumla' => ['lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
            'Bajhang' => ['lat' => 29.7123, 'lng' => 81.2345, 'alt' => 1720],
            'Bajura' => ['lat' => 29.6456, 'lng' => 81.4567, 'alt' => 1800],
            'Dhorpatan' => ['lat' => 28.4500, 'lng' => 83.0500, 'alt' => 2850],
            'Rolwaling' => ['lat' => 27.6456, 'lng' => 86.2456, 'alt' => 2500],

            // ============================================================
            // ✅ API HIMAL (Darchula, Sitapur, Khalanga)
            // ============================================================
            'Darchula' => ['lat' => 29.8456, 'lng' => 80.5345, 'alt' => 700],
            'Sitapur'  => ['lat' => 29.8789, 'lng' => 80.5567, 'alt' => 900],
            'Khalanga' => ['lat' => 29.9123, 'lng' => 80.5789, 'alt' => 1200],

            // ============================================================
            // ✅ API BASE CAMP (tent/camping service)
            // ============================================================
            'Api Base Camp' => ['lat' => 30.0123, 'lng' => 80.6000, 'alt' => 4500],
        ];
    }

    protected function getPriceMap(): array
    {
        return [
            // Existing
            'Simikot' => 25,
            'Rara Lake' => 25,
            'Jumla' => 20,
            'Bajhang' => 20,
            'Bajura' => 20,
            'Dhorpatan' => 20,
            'Rolwaling' => 25,

            // ✅ Api Himal
            'Darchula' => 20,
            'Sitapur'  => 22,
            'Khalanga' => 25,

            // ✅ Api Base Camp (tent/camping, higher price for luxury)
            'Api Base Camp' => 30,
        ];
    }
}