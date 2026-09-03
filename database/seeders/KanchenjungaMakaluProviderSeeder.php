<?php

namespace Database\Seeders;

class KanchenjungaMakaluProviderSeeder extends BaseProviderSeeder
{
    protected function getProviderEmail(): string
    {
        return 'kanchenjunga-providers@travelai.com';
    }

    protected function getProviderName(): string
    {
        return 'Kanchenjunga/Makalu Provider System';
    }

    protected function getLocationData(): array
    {
        return [
            'Suketar' => ['lat' => 27.3456, 'lng' => 87.7123, 'alt' => 2420],
            'Ghunsa' => ['lat' => 27.5456, 'lng' => 87.8456, 'alt' => 3480],
            'Kanchenjunga Base Camp' => ['lat' => 27.6456, 'lng' => 87.9123, 'alt' => 5140],
            'Tumlingtar' => ['lat' => 27.3123, 'lng' => 87.2234, 'alt' => 450],
            'Makalu Base Camp' => ['lat' => 27.5789, 'lng' => 87.4012, 'alt' => 4870],
            'Barun Valley' => ['lat' => 27.5789, 'lng' => 87.4012, 'alt' => 3800],
        ];
    }

    protected function getPriceMap(): array
    {
        return [
            'Suketar' => 20,
            'Ghunsa' => 25,
            'Kanchenjunga Base Camp' => 30,
            'Tumlingtar' => 20,
            'Makalu Base Camp' => 30,
            'Barun Valley' => 30,
        ];
    }
}