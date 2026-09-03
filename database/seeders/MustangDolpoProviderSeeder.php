<?php

namespace Database\Seeders;

class MustangDolpoProviderSeeder extends BaseProviderSeeder
{
    protected function getProviderEmail(): string
    {
        return 'mustang-providers@travelai.com';
    }

    protected function getProviderName(): string
    {
        return 'Mustang/Dolpo Provider System';
    }

    protected function getLocationData(): array
    {
        return [
            'Kagbeni' => ['lat' => 28.8145, 'lng' => 83.7812, 'alt' => 2800],
            'Marpha' => ['lat' => 28.7345, 'lng' => 83.7123, 'alt' => 2670],
            'Lo Manthang' => ['lat' => 28.9456, 'lng' => 83.9123, 'alt' => 3840],
            'Jomsom' => ['lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
            'Muktinath' => ['lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
            'Phoksundo Lake' => ['lat' => 29.4456, 'lng' => 82.8345, 'alt' => 3611],
            'Shey Gompa' => ['lat' => 29.4123, 'lng' => 82.7123, 'alt' => 4100],
        ];
    }

    protected function getPriceMap(): array
    {
        return [
            'Kagbeni' => 25,
            'Marpha' => 25,
            'Lo Manthang' => 35,
            'Jomsom' => 28,
            'Muktinath' => 30,
            'Phoksundo Lake' => 30,
            'Shey Gompa' => 35,
        ];
    }
}