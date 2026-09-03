<?php

namespace Database\Seeders;

class ReligiousSitesProviderSeeder extends BaseProviderSeeder
{
    protected function getProviderEmail(): string
    {
        return 'religious-providers@travelai.com';
    }

    protected function getProviderName(): string
    {
        return 'Religious Sites Provider System';
    }

    protected function getLocationData(): array
    {
        return [
            'Lumbini' => ['lat' => 27.4689, 'lng' => 83.2767, 'alt' => 150],
            'Janakpur' => ['lat' => 26.7234, 'lng' => 85.9234, 'alt' => 74],
            'Muktinath' => ['lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
            'Manakamana' => ['lat' => 27.8234, 'lng' => 84.5678, 'alt' => 1300],
            'Gorkha' => ['lat' => 28.0123, 'lng' => 84.6123, 'alt' => 1135],
            'Patan' => ['lat' => 27.6736, 'lng' => 85.3251, 'alt' => 1400],
            'Bhaktapur' => ['lat' => 27.6722, 'lng' => 85.4295, 'alt' => 1401],
            'Kathmandu' => ['lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
            'Pathibhara' => ['lat' => 27.4123, 'lng' => 87.7567, 'alt' => 3794],
            'Suketar' => ['lat' => 27.3456, 'lng' => 87.7123, 'alt' => 2420],
            'Taplejung' => ['lat' => 27.3456, 'lng' => 87.7123, 'alt' => 2420],
        ];
    }

    protected function getPriceMap(): array
    {
        return [
            'Lumbini' => 25,
            'Janakpur' => 20,
            'Muktinath' => 30,
            'Manakamana' => 22,
            'Gorkha' => 20,
            'Patan' => 25,
            'Bhaktapur' => 25,
            'Kathmandu' => 35,
            'Pathibhara' => 25,
            'Suketar' => 20,
            'Taplejung' => 20,
        ];
    }
}