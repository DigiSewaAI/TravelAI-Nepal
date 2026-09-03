<?php

namespace Database\Seeders;

class HiddenGemsProviderSeeder extends BaseProviderSeeder
{
    protected function getProviderEmail(): string
    {
        return 'hidden-gems-providers@travelai.com';
    }

    protected function getProviderName(): string
    {
        return 'Hidden Gems Provider System';
    }

    protected function getLocationData(): array
    {
        return [
            'Tansen' => ['lat' => 27.8456, 'lng' => 83.5123, 'alt' => 1350],
            'Gorkha' => ['lat' => 28.0123, 'lng' => 84.6123, 'alt' => 1135],
            'Dolakha' => ['lat' => 27.6123, 'lng' => 86.2234, 'alt' => 1900],
            'Sindhuli' => ['lat' => 27.2789, 'lng' => 85.9567, 'alt' => 600],
            'Manakamana' => ['lat' => 27.8234, 'lng' => 84.5678, 'alt' => 1300],
        ];
    }

    protected function getPriceMap(): array
    {
        return [
            'Tansen' => 20,
            'Gorkha' => 20,
            'Dolakha' => 18,
            'Sindhuli' => 18,
            'Manakamana' => 22,
        ];
    }
}