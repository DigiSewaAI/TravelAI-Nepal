<?php

namespace Database\Seeders;

class LangtangProviderSeeder extends BaseProviderSeeder
{
    protected function getProviderEmail(): string
    {
        return 'langtang-providers@travelai.com';
    }

    protected function getProviderName(): string
    {
        return 'Langtang Provider System';
    }

    protected function getLocationData(): array
    {
        return [
            'Syabrubesi' => ['lat' => 28.1579, 'lng' => 85.3378, 'alt' => 1500],
            'Lama Hotel' => ['lat' => 28.1678, 'lng' => 85.3782, 'alt' => 2470],
            'Langtang' => ['lat' => 28.2219, 'lng' => 85.5147, 'alt' => 3430],
            'Kyangjin Gompa' => ['lat' => 28.2567, 'lng' => 85.5234, 'alt' => 3870],
        ];
    }

    protected function getPriceMap(): array
    {
        return [
            'Syabrubesi' => 25,
            'Lama Hotel' => 25,
            'Langtang' => 30,
            'Kyangjin Gompa' => 35,
        ];
    }
}