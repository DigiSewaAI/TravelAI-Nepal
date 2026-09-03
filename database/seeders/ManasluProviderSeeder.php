<?php

namespace Database\Seeders;

class ManasluProviderSeeder extends BaseProviderSeeder
{
    protected function getProviderEmail(): string
    {
        return 'manaslu-providers@travelai.com';
    }

    protected function getProviderName(): string
    {
        return 'Manaslu Provider System';
    }

    protected function getLocationData(): array
    {
        return [
            'Arughat' => ['lat' => 28.0456, 'lng' => 84.8123, 'alt' => 600],
            'Soti Khola' => ['lat' => 28.0789, 'lng' => 84.8345, 'alt' => 700],
            'Machha Khola' => ['lat' => 28.1123, 'lng' => 84.8567, 'alt' => 900],
            'Jagat' => ['lat' => 28.1456, 'lng' => 84.8789, 'alt' => 1350],
            'Deng' => ['lat' => 28.1789, 'lng' => 84.9012, 'alt' => 1800],
            'Namrung' => ['lat' => 28.2123, 'lng' => 84.9234, 'alt' => 2630],
            'Lho' => ['lat' => 28.2456, 'lng' => 84.9456, 'alt' => 3180],
            'Samagaon' => ['lat' => 28.2789, 'lng' => 84.9678, 'alt' => 3530],
            'Samdo' => ['lat' => 28.3123, 'lng' => 84.9901, 'alt' => 3870],
            'Dharamsala' => ['lat' => 28.3456, 'lng' => 85.0123, 'alt' => 4460],
            'Bimthang' => ['lat' => 28.3789, 'lng' => 84.6345, 'alt' => 3720],
            'Tilije' => ['lat' => 28.4123, 'lng' => 84.6567, 'alt' => 2300],
            'Tal' => ['lat' => 28.4456, 'lng' => 84.6789, 'alt' => 1700],
            'Dharapani' => ['lat' => 28.5289, 'lng' => 84.3545, 'alt' => 1860],
        ];
    }

    protected function getPriceMap(): array
    {
        return [
            'Arughat' => 20,
            'Soti Khola' => 20,
            'Machha Khola' => 22,
            'Jagat' => 25,
            'Deng' => 25,
            'Namrung' => 28,
            'Lho' => 30,
            'Samagaon' => 35,
            'Samdo' => 35,
            'Dharamsala' => 30,
            'Bimthang' => 25,
            'Tilije' => 22,
            'Tal' => 20,
            'Dharapani' => 25,
        ];
    }
}