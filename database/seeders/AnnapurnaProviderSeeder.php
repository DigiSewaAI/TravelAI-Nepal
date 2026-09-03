<?php

namespace Database\Seeders;

class AnnapurnaProviderSeeder extends BaseProviderSeeder
{
    protected function getProviderEmail(): string
    {
        return 'annapurna-providers@travelai.com';
    }

    protected function getProviderName(): string
    {
        return 'Annapurna Provider System';
    }

    protected function getLocationData(): array
    {
        return [
            'Besisahar' => ['lat' => 28.2398, 'lng' => 84.3824, 'alt' => 760],
            'Bahundanda' => ['lat' => 28.3312, 'lng' => 84.3601, 'alt' => 1310],
            'Chamche' => ['lat' => 28.4751, 'lng' => 84.3317, 'alt' => 1380],
            'Dharapani' => ['lat' => 28.5289, 'lng' => 84.3545, 'alt' => 1860],
            'Chame' => ['lat' => 28.5581, 'lng' => 84.3587, 'alt' => 2670],
            'Pisang' => ['lat' => 28.6194, 'lng' => 84.2027, 'alt' => 3200],
            'Manang' => ['lat' => 28.6664, 'lng' => 84.1248, 'alt' => 3540],
            'Yak Kharka' => ['lat' => 28.7123, 'lng' => 84.0877, 'alt' => 4010],
            'Thorong Phedi' => ['lat' => 28.7525, 'lng' => 84.0649, 'alt' => 4420],
            // 'Thorong La' => SKIPPED (is_habitable=false) ✅
            'Muktinath' => ['lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
            'Jomsom' => ['lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
            'Tatopani' => ['lat' => 28.6533, 'lng' => 83.6365, 'alt' => 1190],
            'Ghorepani' => ['lat' => 28.4821, 'lng' => 83.7256, 'alt' => 2860],
            'Nayapul' => ['lat' => 28.3986, 'lng' => 83.7123, 'alt' => 1070],
        ];
    }

    protected function getPriceMap(): array
    {
        return [
            'Besisahar' => 25,
            'Bahundanda' => 22,
            'Chamche' => 22,
            'Dharapani' => 28,
            'Chame' => 30,
            'Pisang' => 35,
            'Manang' => 40,
            'Yak Kharka' => 30,
            'Thorong Phedi' => 25,
            // 'Thorong La' => not needed
            'Muktinath' => 30,
            'Jomsom' => 28,
            'Tatopani' => 25,
            'Ghorepani' => 30,
            'Nayapul' => 20,
        ];
    }
}