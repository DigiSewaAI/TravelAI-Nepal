<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            // ============================================================
            // MAJOR CITIES & DISTRICTS (NEPAL)
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Bagmati', 'city' => 'Kathmandu', 'latitude' => 27.7172, 'longitude' => 85.3240],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Pokhara', 'latitude' => 28.2096, 'longitude' => 83.9857],

            // ============================================================
            // ANNAPURNA REGION (with all overnight stops as separate locations)
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Lamjung', 'city' => 'Besisahar', 'latitude' => 28.2398, 'longitude' => 84.3824],
            ['country' => 'Nepal', 'state' => 'Lamjung', 'city' => 'Bahundanda', 'latitude' => 28.3312, 'longitude' => 84.3601],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Chamche', 'latitude' => 28.4751, 'longitude' => 84.3317],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Dharapani', 'latitude' => 28.5289, 'longitude' => 84.3545],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Chame', 'latitude' => 28.5581, 'longitude' => 84.3587],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Pisang', 'latitude' => 28.6194, 'longitude' => 84.2027],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Manang', 'latitude' => 28.6664, 'longitude' => 84.1248],

            // High-altitude stops (separate locations)
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Yak Kharka', 'latitude' => 28.7123, 'longitude' => 84.0877],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Thorong Phedi', 'latitude' => 28.7525, 'longitude' => 84.0649],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Thorong La', 'latitude' => 28.7992, 'longitude' => 84.0081],

            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Muktinath', 'latitude' => 28.8177, 'longitude' => 83.8849],
            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Jomsom', 'latitude' => 28.7850, 'longitude' => 83.7312],
            ['country' => 'Nepal', 'state' => 'Myagdi', 'city' => 'Tatopani', 'latitude' => 28.6533, 'longitude' => 83.6365],
            ['country' => 'Nepal', 'state' => 'Myagdi', 'city' => 'Ghorepani', 'latitude' => 28.4821, 'longitude' => 83.7256],
            ['country' => 'Nepal', 'state' => 'Kaski', 'city' => 'Nayapul', 'latitude' => 28.3986, 'longitude' => 83.7123],
            ['country' => 'Nepal', 'state' => 'Kaski', 'city' => 'Birethanti', 'latitude' => 28.4245, 'longitude' => 83.7564],
            ['country' => 'Nepal', 'state' => 'Myagdi', 'city' => 'Tikhedhunga', 'latitude' => 28.4387, 'longitude' => 83.7105],
            ['country' => 'Nepal', 'state' => 'Myagdi', 'city' => 'Ulleri', 'latitude' => 28.4412, 'longitude' => 83.7221],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Ghandruk', 'latitude' => 28.4681, 'longitude' => 83.8027],
            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Kagbeni', 'latitude' => 28.8145, 'longitude' => 83.7812],
            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Marpha', 'latitude' => 28.7345, 'longitude' => 83.7123],
            ['country' => 'Nepal', 'state' => 'Myagdi', 'city' => 'Ghasa', 'latitude' => 28.6123, 'longitude' => 83.6456],

            // ============================================================
            // EVEREST REGION
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Lukla', 'latitude' => 27.6869, 'longitude' => 86.7314],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Namche', 'latitude' => 27.8042, 'longitude' => 86.7106],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Tengboche', 'latitude' => 27.8361, 'longitude' => 86.7643],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Dingboche', 'latitude' => 27.8927, 'longitude' => 86.8242],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Lobuche', 'latitude' => 27.9358, 'longitude' => 86.8087],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Gorak Shep', 'latitude' => 27.9812, 'longitude' => 86.8274],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Everest Base Camp', 'latitude' => 28.0057, 'longitude' => 86.8294],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Gokyo', 'latitude' => 27.9585, 'longitude' => 86.7428],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Phakding', 'latitude' => 27.7432, 'longitude' => 86.7123],

            // ============================================================
            // LANGTANG REGION
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Syabrubesi', 'latitude' => 28.1579, 'longitude' => 85.3378],
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Langtang', 'latitude' => 28.2219, 'longitude' => 85.5147],
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Gosaikunda', 'latitude' => 28.1784, 'longitude' => 85.3931],
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Dhunche', 'latitude' => 28.0937, 'longitude' => 85.3068],
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Kyangjin Gompa', 'latitude' => 28.2567, 'longitude' => 85.5234],
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Lama Hotel', 'latitude' => 28.1678, 'longitude' => 85.3782],

            // ============================================================
            // MUSTANG / DOLPO
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Lo Manthang', 'latitude' => 28.9456, 'longitude' => 83.9123],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Jumla', 'latitude' => 29.2750, 'longitude' => 82.1589],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Shey Gompa', 'latitude' => 29.4123, 'longitude' => 82.7123],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Phoksundo Lake', 'latitude' => 29.4456, 'longitude' => 82.8345],

            // ============================================================
            // KANCHENJUNGA REGION
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Taplejung', 'city' => 'Suketar', 'latitude' => 27.3456, 'longitude' => 87.7123],
            ['country' => 'Nepal', 'state' => 'Taplejung', 'city' => 'Ghunsa', 'latitude' => 27.5456, 'longitude' => 87.8456],
            ['country' => 'Nepal', 'state' => 'Taplejung', 'city' => 'Kanchenjunga Base Camp', 'latitude' => 27.6456, 'longitude' => 87.9123],

            // ============================================================
            // MAKALU REGION
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Sankhuwasabha', 'city' => 'Tumlingtar', 'latitude' => 27.3123, 'longitude' => 87.2234],
            ['country' => 'Nepal', 'state' => 'Sankhuwasabha', 'city' => 'Makalu Base Camp', 'latitude' => 27.5789, 'longitude' => 87.4012],
            ['country' => 'Nepal', 'state' => 'Sankhuwasabha', 'city' => 'Barun Valley', 'latitude' => 27.5789, 'longitude' => 87.4012],

            // ============================================================
            // MANASLU REGION (ADDED)
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Arughat', 'latitude' => 28.0456, 'longitude' => 84.8123],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Soti Khola', 'latitude' => 28.0789, 'longitude' => 84.8345],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Machha Khola', 'latitude' => 28.1123, 'longitude' => 84.8567],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Jagat', 'latitude' => 28.1456, 'longitude' => 84.8789],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Deng', 'latitude' => 28.1789, 'longitude' => 84.9012],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Namrung', 'latitude' => 28.2123, 'longitude' => 84.9234],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Lho', 'latitude' => 28.2456, 'longitude' => 84.9456],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Samagaon', 'latitude' => 28.2789, 'longitude' => 84.9678],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Samdo', 'latitude' => 28.3123, 'longitude' => 84.9901],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Dharamsala', 'latitude' => 28.3456, 'longitude' => 85.0123],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Bimthang', 'latitude' => 28.3789, 'longitude' => 84.6345],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Tilije', 'latitude' => 28.4123, 'longitude' => 84.6567],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Tal', 'latitude' => 28.4456, 'longitude' => 84.6789],

            // ============================================================
            // OTHER REMOTE REGIONS
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Humla', 'city' => 'Simikot', 'latitude' => 29.9789, 'longitude' => 82.0123],
            ['country' => 'Nepal', 'state' => 'Myagdi', 'city' => 'Dhaulagiri', 'latitude' => 28.8456, 'longitude' => 83.7012],
            ['country' => 'Nepal', 'state' => 'Bajhang', 'city' => 'Bajhang', 'latitude' => 29.7123, 'longitude' => 81.2345],
            ['country' => 'Nepal', 'state' => 'Bajura', 'city' => 'Bajura', 'latitude' => 29.6456, 'longitude' => 81.4567],

            // ============================================================
            // NATIONAL PARKS & CITIES
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Chitwan', 'city' => 'Chitwan', 'latitude' => 27.5789, 'longitude' => 84.4567],
            ['country' => 'Nepal', 'state' => 'Chitwan', 'city' => 'Sauraha', 'latitude' => 27.5789, 'longitude' => 84.4567],
            ['country' => 'Nepal', 'state' => 'Kanchanpur', 'city' => 'Kanchanpur', 'latitude' => 28.8234, 'longitude' => 80.4567],
            ['country' => 'Nepal', 'state' => 'Banke', 'city' => 'Nepalgunj', 'latitude' => 28.0456, 'longitude' => 81.6123],

            // ============================================================
            // HISTORICAL / RELIGIOUS SITES
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Lumbini', 'city' => 'Lumbini', 'latitude' => 27.4689, 'longitude' => 83.2767],
            ['country' => 'Nepal', 'state' => 'Dhanusa', 'city' => 'Janakpur', 'latitude' => 26.7234, 'longitude' => 85.9234],
            ['country' => 'Nepal', 'state' => 'Kapilavastu', 'city' => 'Kapilavastu', 'latitude' => 27.5345, 'longitude' => 83.0234],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Gorkha', 'latitude' => 28.0123, 'longitude' => 84.6123],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Manakamana', 'latitude' => 27.8234, 'longitude' => 84.5678],
// ============================================================
// RELIGIOUS SITES - Pathibhara Temple
// ============================================================
['country' => 'Nepal', 'state' => 'Taplejung', 'city' => 'Pathibhara', 'latitude' => 27.3456, 'longitude' => 87.7123],
['country' => 'Nepal', 'state' => 'Taplejung', 'city' => 'Taplejung', 'latitude' => 27.3456, 'longitude' => 87.7123],
['country' => 'Nepal', 'state' => 'Taplejung', 'city' => 'Suketar', 'latitude' => 27.3456, 'longitude' => 87.7123],
            // ============================================================
            // VALLEY SURROUNDINGS (Kathmandu Valley)
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Tanahu', 'city' => 'Bandipur', 'latitude' => 27.9123, 'longitude' => 84.4123],
            ['country' => 'Nepal', 'state' => 'Palpa', 'city' => 'Tansen', 'latitude' => 27.8456, 'longitude' => 83.5123],
            ['country' => 'Nepal', 'state' => 'Kavrepalanchok', 'city' => 'Dhulikhel', 'latitude' => 27.6223, 'longitude' => 85.5456],
            ['country' => 'Nepal', 'state' => 'Kavrepalanchok', 'city' => 'Panauti', 'latitude' => 27.6123, 'longitude' => 85.5345],
            ['country' => 'Nepal', 'state' => 'Kathmandu', 'city' => 'Kirtipur', 'latitude' => 27.6756, 'longitude' => 85.2789],
            ['country' => 'Nepal', 'state' => 'Sindhupalchok', 'city' => 'Sankhu', 'latitude' => 27.7345, 'longitude' => 85.4567],
            ['country' => 'Nepal', 'state' => 'Lalitpur', 'city' => 'Khokana', 'latitude' => 27.6456, 'longitude' => 85.2989],
            ['country' => 'Nepal', 'state' => 'Lalitpur', 'city' => 'Bungamati', 'latitude' => 27.6345, 'longitude' => 85.3123],
            ['country' => 'Nepal', 'state' => 'Bhaktapur', 'city' => 'Bhaktapur', 'latitude' => 27.6722, 'longitude' => 85.4295],
            ['country' => 'Nepal', 'state' => 'Lalitpur', 'city' => 'Patan', 'latitude' => 27.6736, 'longitude' => 85.3251],
            ['country' => 'Nepal', 'state' => 'Bhaktapur', 'city' => 'Changunarayan', 'latitude' => 27.7123, 'longitude' => 85.4234],
            ['country' => 'Nepal', 'state' => 'Bagmati', 'city' => 'Nagarkot', 'latitude' => 27.7145, 'longitude' => 85.5234],

            // ============================================================
            // REMOTE AREAS
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Lumbini', 'city' => 'Dhorpatan', 'latitude' => 28.4500, 'longitude' => 83.0500],            ['country' => 'Nepal', 'state' => 'Dolakha', 'city' => 'Dolakha', 'latitude' => 27.6123, 'longitude' => 86.2234],
            ['country' => 'Nepal', 'state' => 'Rolwaling', 'city' => 'Rolwaling', 'latitude' => 27.6456, 'longitude' => 86.2456],
            ['country' => 'Nepal', 'state' => 'Mugu', 'city' => 'Rara Lake', 'latitude' => 29.3789, 'longitude' => 82.3891],
            ['country' => 'Nepal', 'state' => 'Bardiya', 'city' => 'Bardiya', 'latitude' => 28.3123, 'longitude' => 81.4234],
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Rasuwa Gadhi', 'latitude' => 28.3156, 'longitude' => 85.0456],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Nar', 'latitude' => 28.6127, 'longitude' => 84.2108],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Phu', 'latitude' => 28.6512, 'longitude' => 84.1589],

            // ============================================================
            // ✅ यी locations हुन् (यी राख्नुहोस्, यदि छैनन् भने)
['country' => 'Nepal', 'state' => 'Bagmati', 'city' => 'Trishuli River', 'latitude' => 27.9123, 'longitude' => 84.8123],
['country' => 'Nepal', 'state' => 'Sindhupalchok', 'city' => 'Bhote Koshi River', 'latitude' => 27.8456, 'longitude' => 85.9234],
['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Kali Gandaki River', 'latitude' => 28.7345, 'longitude' => 83.7123],
['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Seti River', 'latitude' => 28.2096, 'longitude' => 83.9857],
['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Sarangkot', 'latitude' => 28.2456, 'longitude' => 83.9456],
['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Kusma Bridge', 'latitude' => 28.2096, 'longitude' => 83.9857],

// ============================================================
// HIDDEN GEMS
// ============================================================
['country' => 'Nepal', 'state' => 'Sindhuli', 'city' => 'Sindhuli', 'latitude' => 27.2789, 'longitude' => 85.9567],
        ];

        foreach ($locations as $loc) {
            Location::firstOrCreate(
                ['city' => $loc['city']],
                $loc
            );
        }

        $this->command->info('✅ LocationSeeder completed: ' . count($locations) . ' locations.');
        $this->command->info('   📌 Manaslu locations added (Arughat, Soti Khola, Machha Khola, Jagat, Deng, Namrung, Lho, Samagaon, Samdo, Dharamsala, Bimthang, Tilije, Tal).');
    }
}