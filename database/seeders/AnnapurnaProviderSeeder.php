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
            // --- Annapurna Circuit (पहिले देखि) ---
            'Besisahar' => ['lat' => 28.2398, 'lng' => 84.3824, 'alt' => 760],
            'Bahundanda' => ['lat' => 28.3312, 'lng' => 84.3601, 'alt' => 1310],
            'Chamche' => ['lat' => 28.4751, 'lng' => 84.3317, 'alt' => 1380],
            'Dharapani' => ['lat' => 28.5289, 'lng' => 84.3545, 'alt' => 1860],
            'Chame' => ['lat' => 28.5581, 'lng' => 84.3587, 'alt' => 2670],
            'Pisang' => ['lat' => 28.6194, 'lng' => 84.2027, 'alt' => 3200],
            'Manang' => ['lat' => 28.6664, 'lng' => 84.1248, 'alt' => 3540],
            'Yak Kharka' => ['lat' => 28.7123, 'lng' => 84.0877, 'alt' => 4010],
            'Thorong Phedi' => ['lat' => 28.7525, 'lng' => 84.0649, 'alt' => 4420],
            // 'Thorong La' => SKIPPED (is_habitable=false)
            'Muktinath' => ['lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
            'Jomsom' => ['lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
            'Tatopani' => ['lat' => 28.6533, 'lng' => 83.6365, 'alt' => 1190],
            'Ghorepani' => ['lat' => 28.4821, 'lng' => 83.7256, 'alt' => 2860],
            'Nayapul' => ['lat' => 28.3986, 'lng' => 83.7123, 'alt' => 1070],

            // --- Poon Hill / ABC (पहिले नै थपिएका) ---
            'Tikhedhunga' => ['lat' => 28.4387, 'lng' => 83.7105, 'alt' => 1540],
            'Ulleri'      => ['lat' => 28.4412, 'lng' => 83.7221, 'alt' => 1960],
            'Ghandruk'    => ['lat' => 28.4681, 'lng' => 83.8027, 'alt' => 1940],
            'Ghasa'       => ['lat' => 28.6123, 'lng' => 83.6456, 'alt' => 2010],

            // --- ✅ थपिएका: ABC को बाँकी locations ---
            'Tadapani' => ['lat' => 28.4167, 'lng' => 83.8333, 'alt' => 2630],
            'Chhomrong' => ['lat' => 28.4583, 'lng' => 83.8833, 'alt' => 2170],
            'Sinuwa' => ['lat' => 28.5000, 'lng' => 83.9167, 'alt' => 2360],
            'Bamboo' => ['lat' => 28.5333, 'lng' => 83.9500, 'alt' => 2335],
            'Dovan' => ['lat' => 28.5667, 'lng' => 83.9833, 'alt' => 2500],
            'Himalaya' => ['lat' => 28.6000, 'lng' => 84.0167, 'alt' => 2920],
            'Deurali' => ['lat' => 28.6333, 'lng' => 84.0500, 'alt' => 3230],
            'Machhapuchhre Base Camp' => ['lat' => 28.6667, 'lng' => 84.0833, 'alt' => 3700],
            'Annapurna Base Camp' => ['lat' => 28.7000, 'lng' => 84.1167, 'alt' => 4130],

            // --- ✅ थपिएका: Mardi Himal locations ---
            'Pothana' => ['lat' => 28.3714, 'lng' => 83.8309, 'alt' => 1900],
            'Forest Camp' => ['lat' => 28.3915, 'lng' => 83.7912, 'alt' => 2500],
            'Low Camp' => ['lat' => 28.3997, 'lng' => 83.7745, 'alt' => 3150],
            'High Camp' => ['lat' => 28.4089, 'lng' => 83.7593, 'alt' => 3580],
            'Mardi Himal Base Camp' => ['lat' => 28.4201, 'lng' => 83.7482, 'alt' => 4500],
            'Siding Village' => ['lat' => 28.3442, 'lng' => 83.7109, 'alt' => 1700],
        ];
    }

    protected function getPriceMap(): array
    {
        return [
            // Annapurna Circuit
            'Besisahar' => 25,
            'Bahundanda' => 22,
            'Chamche' => 22,
            'Dharapani' => 28,
            'Chame' => 30,
            'Pisang' => 35,
            'Manang' => 40,
            'Yak Kharka' => 30,
            'Thorong Phedi' => 25,
            'Muktinath' => 30,
            'Jomsom' => 28,
            'Tatopani' => 25,
            'Ghorepani' => 30,
            'Nayapul' => 20,

            // Poon Hill / ABC extra
            'Tikhedhunga' => 22,
            'Ulleri'      => 25,
            'Ghandruk'    => 25,
            'Ghasa'       => 22,

            // ✅ ABC बाँकी
            'Tadapani' => 25,
            'Chhomrong' => 28,
            'Sinuwa' => 28,
            'Bamboo' => 22,
            'Dovan' => 22,
            'Himalaya' => 25,
            'Deurali' => 25,
            'Machhapuchhre Base Camp' => 30,
            'Annapurna Base Camp' => 35,

            // ✅ Mardi Himal
            'Pothana' => 22,
            'Forest Camp' => 22,
            'Low Camp' => 25,
            'High Camp' => 28,
            'Mardi Himal Base Camp' => 30,
            'Siding Village' => 20,
        ];
    }
}