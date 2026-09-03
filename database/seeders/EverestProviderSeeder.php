<?php

namespace Database\Seeders;

class EverestProviderSeeder extends BaseProviderSeeder
{
    protected function getProviderEmail(): string
    {
        return 'everest-providers@travelai.com';
    }

    protected function getProviderName(): string
    {
        return 'Everest Provider System';
    }

    protected function getLocationData(): array
    {
        return [
            'Lukla' => ['lat' => 27.6869, 'lng' => 86.7314, 'alt' => 2860],
            'Phakding' => ['lat' => 27.7432, 'lng' => 86.7123, 'alt' => 2610],
            'Namche' => ['lat' => 27.8042, 'lng' => 86.7106, 'alt' => 3440],
            'Tengboche' => ['lat' => 27.8361, 'lng' => 86.7643, 'alt' => 3860],
            'Dingboche' => ['lat' => 27.8927, 'lng' => 86.8242, 'alt' => 4410],
            'Lobuche' => ['lat' => 27.9358, 'lng' => 86.8087, 'alt' => 4940],
            'Gorak Shep' => ['lat' => 27.9812, 'lng' => 86.8274, 'alt' => 5140],
            // 'Everest Base Camp' is skipped (is_habitable = false)
        ];
    }

    protected function getPriceMap(): array
    {
        return [
            'Lukla' => 35,
            'Phakding' => 30,
            'Namche' => 50,
            'Tengboche' => 45,
            'Dingboche' => 40,
            'Lobuche' => 35,
            'Gorak Shep' => 30,
        ];
    }
}