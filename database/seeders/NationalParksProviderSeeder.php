<?php

namespace Database\Seeders;

class NationalParksProviderSeeder extends BaseProviderSeeder
{
    protected function getProviderEmail(): string
    {
        return 'national-parks-providers@travelai.com';
    }

    protected function getProviderName(): string
    {
        return 'National Parks Provider System';
    }

    protected function getLocationData(): array
    {
        return [
            'Chitwan' => ['lat' => 27.5789, 'lng' => 84.4567, 'alt' => 415],
            'Bardiya' => ['lat' => 28.3123, 'lng' => 81.4234, 'alt' => 200],
            'Sauraha' => ['lat' => 27.5789, 'lng' => 84.4567, 'alt' => 415],
            'Kanchanpur' => ['lat' => 28.8234, 'lng' => 80.4567, 'alt' => 150],
            'Dhorpatan' => ['lat' => 28.4500, 'lng' => 83.0500, 'alt' => 2850],
        ];
    }

    protected function getPriceMap(): array
    {
        return [
            'Chitwan' => 30,
            'Bardiya' => 25,
            'Sauraha' => 30,
            'Kanchanpur' => 20,
            'Dhorpatan' => 20,
        ];
    }
}