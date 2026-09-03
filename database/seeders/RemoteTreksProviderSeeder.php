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
            'Simikot' => ['lat' => 29.9789, 'lng' => 82.0123, 'alt' => 2910],
            'Rara Lake' => ['lat' => 29.3789, 'lng' => 82.3891, 'alt' => 2990],
            'Jumla' => ['lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
            'Bajhang' => ['lat' => 29.7123, 'lng' => 81.2345, 'alt' => 1720],
            'Bajura' => ['lat' => 29.6456, 'lng' => 81.4567, 'alt' => 1800],
            'Dhorpatan' => ['lat' => 28.4500, 'lng' => 83.0500, 'alt' => 2850],
            'Rolwaling' => ['lat' => 27.6456, 'lng' => 86.2456, 'alt' => 2500],
        ];
    }

    protected function getPriceMap(): array
    {
        return [
            'Simikot' => 25,
            'Rara Lake' => 25,
            'Jumla' => 20,
            'Bajhang' => 20,
            'Bajura' => 20,
            'Dhorpatan' => 20,
            'Rolwaling' => 25,
        ];
    }
}