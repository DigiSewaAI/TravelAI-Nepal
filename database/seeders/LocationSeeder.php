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
            ['country' => 'Nepal', 'state' => 'Bagmati', 'city' => 'Kathmandu', 'latitude' => 27.7172, 'longitude' => 85.3240, 'is_habitable' => true],
                        // Phase 4R-fix-5: 4 villages missing location entries
            ['country' => 'Nepal', 'state' => 'Taplejung', 'city' => 'Tseram', 'latitude' => 27.5485, 'longitude' => 88.0012, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Dzongla', 'latitude' => 27.9473, 'longitude' => 86.7720, 'is_habitable' => true],
                        // Phase 4R-fix-8: BC locations for Dhaulagiri & Saipal
            ['country' => 'Nepal', 'state' => 'Myagdi', 'city' => 'Dhaulagiri Base Camp', 'latitude' => 28.6964, 'longitude' => 83.4917, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Bajhang', 'city' => 'Saipal Base Camp', 'latitude' => 29.8667, 'longitude' => 81.2167, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Thagnak', 'latitude' => 27.9045, 'longitude' => 86.7180, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Bagmati', 'city' => 'Nuwakot', 'latitude' => 27.9100, 'longitude' => 85.1650, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Pokhara', 'latitude' => 28.2096, 'longitude' => 83.9857, 'is_habitable' => true],

            // ============================================================
            // ANNAPURNA REGION
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Lamjung', 'city' => 'Besisahar', 'latitude' => 28.2398, 'longitude' => 84.3824, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Lamjung', 'city' => 'Bahundanda', 'latitude' => 28.3312, 'longitude' => 84.3601, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Chamche', 'latitude' => 28.4751, 'longitude' => 84.3317, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Dharapani', 'latitude' => 28.5289, 'longitude' => 84.3545, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Chame', 'latitude' => 28.5581, 'longitude' => 84.3587, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Pisang', 'latitude' => 28.6194, 'longitude' => 84.2027, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Manang', 'latitude' => 28.6664, 'longitude' => 84.1248, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Yak Kharka', 'latitude' => 28.7123, 'longitude' => 84.0877, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Thorong Phedi', 'latitude' => 28.7525, 'longitude' => 84.0649, 'is_habitable' => true],
            // ❌ Thorong La – non-habitable (pass only)
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Thorong La', 'latitude' => 28.7992, 'longitude' => 84.0081, 'is_habitable' => false],

            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Muktinath', 'latitude' => 28.8177, 'longitude' => 83.8849, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Jomsom', 'latitude' => 28.7850, 'longitude' => 83.7312, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Myagdi', 'city' => 'Tatopani', 'latitude' => 28.6533, 'longitude' => 83.6365, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Myagdi', 'city' => 'Ghorepani', 'latitude' => 28.4821, 'longitude' => 83.7256, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Kaski', 'city' => 'Nayapul', 'latitude' => 28.3986, 'longitude' => 83.7123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Kaski', 'city' => 'Birethanti', 'latitude' => 28.4245, 'longitude' => 83.7564, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Myagdi', 'city' => 'Tikhedhunga', 'latitude' => 28.4387, 'longitude' => 83.7105, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Myagdi', 'city' => 'Ulleri', 'latitude' => 28.4412, 'longitude' => 83.7221, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Ghandruk', 'latitude' => 28.4681, 'longitude' => 83.8027, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Kagbeni', 'latitude' => 28.8145, 'longitude' => 83.7812, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Marpha', 'latitude' => 28.7345, 'longitude' => 83.7123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Myagdi', 'city' => 'Ghasa', 'latitude' => 28.6123, 'longitude' => 83.6456, 'is_habitable' => true],

            // ============================================================
            // ABC, Mardi Himal (additional locations)
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Tadapani', 'latitude' => 28.5107, 'longitude' => 83.7435, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Chhomrong', 'latitude' => 28.5332, 'longitude' => 83.7589, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Sinuwa', 'latitude' => 28.5436, 'longitude' => 83.7651, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Bamboo', 'latitude' => 28.5549, 'longitude' => 83.7722, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Dovan', 'latitude' => 28.5658, 'longitude' => 83.7786, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Himalaya', 'latitude' => 28.5753, 'longitude' => 83.7834, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Deurali', 'latitude' => 28.5844, 'longitude' => 83.7893, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Machhapuchhre Base Camp', 'latitude' => 28.5923, 'longitude' => 83.7956, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Annapurna Base Camp', 'latitude' => 28.6005, 'longitude' => 83.8001, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Pothana', 'latitude' => 28.3714, 'longitude' => 83.8309, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Forest Camp', 'latitude' => 28.3915, 'longitude' => 83.7912, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Low Camp', 'latitude' => 28.3997, 'longitude' => 83.7745, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'High Camp', 'latitude' => 28.4089, 'longitude' => 83.7593, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Mardi Himal Base Camp', 'latitude' => 28.4201, 'longitude' => 83.7482, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Siding Village', 'latitude' => 28.3442, 'longitude' => 83.7109, 'is_habitable' => true],

            // ============================================================
            // EVEREST REGION
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Lukla', 'latitude' => 27.6869, 'longitude' => 86.7314, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Namche', 'latitude' => 27.8042, 'longitude' => 86.7106, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Tengboche', 'latitude' => 27.8361, 'longitude' => 86.7643, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Dingboche', 'latitude' => 27.8927, 'longitude' => 86.8242, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Lobuche', 'latitude' => 27.9358, 'longitude' => 86.8087, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Gorak Shep', 'latitude' => 27.9812, 'longitude' => 86.8274, 'is_habitable' => true],
            // ❌ Everest Base Camp – non-habitable
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Everest Base Camp', 'latitude' => 28.0057, 'longitude' => 86.8294, 'is_habitable' => false],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Gokyo', 'latitude' => 27.9585, 'longitude' => 86.7428, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Solukhumbu', 'city' => 'Phakding', 'latitude' => 27.7432, 'longitude' => 86.7123, 'is_habitable' => true],

            // ============================================================
            // LANGTANG REGION
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Syabrubesi', 'latitude' => 28.1579, 'longitude' => 85.3378, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Langtang', 'latitude' => 28.2219, 'longitude' => 85.5147, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Gosaikunda', 'latitude' => 28.1784, 'longitude' => 85.3931, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Dhunche', 'latitude' => 28.0937, 'longitude' => 85.3068, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Kyangjin Gompa', 'latitude' => 28.2567, 'longitude' => 85.5234, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Lama Hotel', 'latitude' => 28.1678, 'longitude' => 85.3782, 'is_habitable' => true],

            // ============================================================
            // MUSTANG / DOLPO
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Lo Manthang', 'latitude' => 28.9456, 'longitude' => 83.9123, 'is_habitable' => true],
            // ✅ Jumla is its own district, not Dolpa
            ['country' => 'Nepal', 'state' => 'Jumla', 'city' => 'Jumla', 'latitude' => 29.2750, 'longitude' => 82.1589, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Shey Gompa', 'latitude' => 29.4123, 'longitude' => 82.7123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Phoksundo Lake', 'latitude' => 29.4456, 'longitude' => 82.8345, 'is_habitable' => true],

            // ============================================================
            // KANCHENJUNGA REGION
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Taplejung', 'city' => 'Suketar', 'latitude' => 27.3456, 'longitude' => 87.7123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Taplejung', 'city' => 'Ghunsa', 'latitude' => 27.5456, 'longitude' => 87.8456, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Taplejung', 'city' => 'Kanchenjunga Base Camp', 'latitude' => 27.6456, 'longitude' => 87.9123, 'is_habitable' => true],

            // ============================================================
            // MAKALU REGION
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Sankhuwasabha', 'city' => 'Tumlingtar', 'latitude' => 27.3123, 'longitude' => 87.2234, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Sankhuwasabha', 'city' => 'Makalu Base Camp', 'latitude' => 27.5789, 'longitude' => 87.4012, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Sankhuwasabha', 'city' => 'Barun Valley', 'latitude' => 27.5789, 'longitude' => 87.4012, 'is_habitable' => true],

            // ============================================================
            // MANASLU REGION
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Arughat', 'latitude' => 28.0456, 'longitude' => 84.8123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Soti Khola', 'latitude' => 28.0789, 'longitude' => 84.8345, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Machha Khola', 'latitude' => 28.1123, 'longitude' => 84.8567, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Jagat', 'latitude' => 28.1456, 'longitude' => 84.8789, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Deng', 'latitude' => 28.1789, 'longitude' => 84.9012, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Namrung', 'latitude' => 28.2123, 'longitude' => 84.9234, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Lho', 'latitude' => 28.2456, 'longitude' => 84.9456, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Samagaon', 'latitude' => 28.2789, 'longitude' => 84.9678, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Samdo', 'latitude' => 28.3123, 'longitude' => 84.9901, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Dharamsala', 'latitude' => 28.3456, 'longitude' => 85.0123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Bimthang', 'latitude' => 28.3789, 'longitude' => 84.6345, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Tilije', 'latitude' => 28.4123, 'longitude' => 84.6567, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Tal', 'latitude' => 28.4456, 'longitude' => 84.6789, 'is_habitable' => true],

            // ============================================================
            // API HIMAL REGION
            // ============================================================
            // ✅ Darchula is its own district
            ['country' => 'Nepal', 'state' => 'Darchula', 'city' => 'Darchula', 'latitude' => 29.8456, 'longitude' => 80.5345, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Darchula', 'city' => 'Sitapur', 'latitude' => 29.8789, 'longitude' => 80.5567, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Darchula', 'city' => 'Khalanga', 'latitude' => 29.9123, 'longitude' => 80.5789, 'is_habitable' => true],
            // ✅ Api Base Camp – habitable (tent/camping)
            ['country' => 'Nepal', 'state' => 'Darchula', 'city' => 'Api Base Camp', 'latitude' => 30.0123, 'longitude' => 80.6000, 'is_habitable' => true],
            // Additional waypoints for Api Himal trek
            ['country' => 'Nepal', 'state' => 'Darchula', 'city' => 'Gokuleshwar', 'latitude' => 29.8356, 'longitude' => 80.5345, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Darchula', 'city' => 'Bitule', 'latitude' => 29.8456, 'longitude' => 80.5567, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Darchula', 'city' => 'Khandeshwari', 'latitude' => 29.8678, 'longitude' => 80.5789, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Darchula', 'city' => 'Chiureni', 'latitude' => 29.8891, 'longitude' => 80.6012, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Darchula', 'city' => 'Makarigaun', 'latitude' => 29.9123, 'longitude' => 80.6234, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Darchula', 'city' => 'Seti (Api)', 'latitude' => 29.9345, 'longitude' => 80.6456, 'is_habitable' => true],

            // ============================================================
            // OTHER REMOTE REGIONS
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Humla', 'city' => 'Simikot', 'latitude' => 29.9789, 'longitude' => 82.0123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Myagdi', 'city' => 'Dhaulagiri', 'latitude' => 28.8456, 'longitude' => 83.7012, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Bajhang', 'city' => 'Bajhang', 'latitude' => 29.7123, 'longitude' => 81.2345, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Bajura', 'city' => 'Bajura', 'latitude' => 29.6456, 'longitude' => 81.4567, 'is_habitable' => true],

            // ============================================================
            // NATIONAL PARKS & CITIES
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Chitwan', 'city' => 'Chitwan', 'latitude' => 27.5789, 'longitude' => 84.4567, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Chitwan', 'city' => 'Sauraha', 'latitude' => 27.5789, 'longitude' => 84.4567, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Kanchanpur', 'city' => 'Kanchanpur', 'latitude' => 28.8234, 'longitude' => 80.4567, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Banke', 'city' => 'Nepalgunj', 'latitude' => 28.0456, 'longitude' => 81.6123, 'is_habitable' => true],

            // ============================================================
            // HISTORICAL / RELIGIOUS SITES
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Lumbini', 'city' => 'Lumbini', 'latitude' => 27.4689, 'longitude' => 83.2767, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dhanusa', 'city' => 'Janakpur', 'latitude' => 26.7234, 'longitude' => 85.9234, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Kapilavastu', 'city' => 'Kapilavastu', 'latitude' => 27.5345, 'longitude' => 83.0234, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Gorkha', 'latitude' => 28.0123, 'longitude' => 84.6123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gorkha', 'city' => 'Manakamana', 'latitude' => 27.8234, 'longitude' => 84.5678, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Taplejung', 'city' => 'Pathibhara', 'latitude' => 27.3456, 'longitude' => 87.7123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Taplejung', 'city' => 'Taplejung', 'latitude' => 27.3456, 'longitude' => 87.7123, 'is_habitable' => true],

            // ============================================================
            // VALLEY SURROUNDINGS (Kathmandu Valley)
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Tanahu', 'city' => 'Bandipur', 'latitude' => 27.9123, 'longitude' => 84.4123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Palpa', 'city' => 'Tansen', 'latitude' => 27.8456, 'longitude' => 83.5123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Kavrepalanchok', 'city' => 'Dhulikhel', 'latitude' => 27.6223, 'longitude' => 85.5456, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Kavrepalanchok', 'city' => 'Panauti', 'latitude' => 27.6123, 'longitude' => 85.5345, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Kathmandu', 'city' => 'Kirtipur', 'latitude' => 27.6756, 'longitude' => 85.2789, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Sindhupalchok', 'city' => 'Sankhu', 'latitude' => 27.7345, 'longitude' => 85.4567, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Lalitpur', 'city' => 'Khokana', 'latitude' => 27.6456, 'longitude' => 85.2989, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Lalitpur', 'city' => 'Bungamati', 'latitude' => 27.6345, 'longitude' => 85.3123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Bhaktapur', 'city' => 'Bhaktapur', 'latitude' => 27.6722, 'longitude' => 85.4295, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Lalitpur', 'city' => 'Patan', 'latitude' => 27.6736, 'longitude' => 85.3251, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Bhaktapur', 'city' => 'Changunarayan', 'latitude' => 27.7123, 'longitude' => 85.4234, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Bagmati', 'city' => 'Nagarkot', 'latitude' => 27.7145, 'longitude' => 85.5234, 'is_habitable' => true],

            // ============================================================
            // REMOTE AREAS
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Lumbini', 'city' => 'Dhorpatan', 'latitude' => 28.4500, 'longitude' => 83.0500, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolakha', 'city' => 'Dolakha', 'latitude' => 27.6123, 'longitude' => 86.2234, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Rolwaling', 'city' => 'Rolwaling', 'latitude' => 27.6456, 'longitude' => 86.2456, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Mugu', 'city' => 'Rara Lake', 'latitude' => 29.3789, 'longitude' => 82.3891, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Bardiya', 'city' => 'Bardiya', 'latitude' => 28.3123, 'longitude' => 81.4234, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Rasuwa', 'city' => 'Rasuwa Gadhi', 'latitude' => 28.3156, 'longitude' => 85.0456, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Nar', 'latitude' => 28.6127, 'longitude' => 84.2108, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Manang', 'city' => 'Phu', 'latitude' => 28.6512, 'longitude' => 84.1589, 'is_habitable' => true],

            // ============================================================
            // ADDITIONAL RIVERS & VIEWPOINTS
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Bagmati', 'city' => 'Trishuli River', 'latitude' => 27.9123, 'longitude' => 84.8123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Sindhupalchok', 'city' => 'Bhote Koshi River', 'latitude' => 27.8456, 'longitude' => 85.9234, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Kali Gandaki River', 'latitude' => 28.7345, 'longitude' => 83.7123, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Seti River', 'latitude' => 28.2096, 'longitude' => 83.9857, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Sarangkot', 'latitude' => 28.2456, 'longitude' => 83.9456, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Gandaki', 'city' => 'Kusma Bridge', 'latitude' => 28.2096, 'longitude' => 83.9857, 'is_habitable' => true],

                        // ============================================================
            // DOLPO REGION — Batch 2 canonical locations
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Jumla', 'city' => 'Chhetra', 'latitude' => 29.30, 'longitude' => 82.22, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Jumla', 'city' => 'Chaurikot', 'latitude' => 29.34, 'longitude' => 82.28, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Jumla', 'city' => 'Kagmara Phedi', 'latitude' => 29.38, 'longitude' => 82.36, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Jumla', 'city' => 'Kagmara La', 'latitude' => 29.41, 'longitude' => 82.42, 'is_habitable' => false],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Pungmo', 'latitude' => 29.43, 'longitude' => 82.50, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Phoksundo Bhanjyang', 'latitude' => 29.47, 'longitude' => 82.86, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Ringmo', 'latitude' => 29.45, 'longitude' => 82.85, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Nisal', 'latitude' => 29.50, 'longitude' => 83.02, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Jeng La', 'latitude' => 29.46, 'longitude' => 83.08, 'is_habitable' => false],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Tokyu Gaon', 'latitude' => 29.42, 'longitude' => 83.12, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Dho Tarap', 'latitude' => 29.35, 'longitude' => 83.15, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Chharka', 'latitude' => 29.25, 'longitude' => 83.20, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Sangda La', 'latitude' => 29.10, 'longitude' => 83.30, 'is_habitable' => false],
            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Sangda Village', 'latitude' => 28.95, 'longitude' => 83.45, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Chhepka', 'latitude' => 29.30, 'longitude' => 82.90, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Dunai', 'latitude' => 29.15, 'longitude' => 82.95, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolpa', 'city' => 'Juphal', 'latitude' => 29.10, 'longitude' => 82.98, 'is_habitable' => true],

                        // ============================================================
            // BATCH 3 — Makalu-Barun + Damodar Kunda
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Sankhuwasabha', 'city' => 'Langmale Kharka', 'latitude' => 27.56, 'longitude' => 87.39, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Sankhuwasabha', 'city' => 'Barun Pokhari', 'latitude' => 27.62, 'longitude' => 87.43, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Yara', 'latitude' => 29.02, 'longitude' => 83.85, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Mustang', 'city' => 'Ghara', 'latitude' => 28.99, 'longitude' => 83.88, 'is_habitable' => true],

                        // ============================================================
            // BATCH 4 — Humla / Rolwaling / Saipal intermediate stops
            // ============================================================
            // Humla — Karnali valley
            ['country' => 'Nepal', 'state' => 'Humla', 'city' => 'Chhipra',  'latitude' => 29.92, 'longitude' => 81.95, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Humla', 'city' => 'Torpa',    'latitude' => 29.88, 'longitude' => 81.98, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Humla', 'city' => 'Kermi',    'latitude' => 29.85, 'longitude' => 82.02, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Humla', 'city' => 'Yalbang',  'latitude' => 29.83, 'longitude' => 82.04, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Humla', 'city' => 'Muchu',    'latitude' => 29.80, 'longitude' => 82.06, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Humla', 'city' => 'Yari',     'latitude' => 29.79, 'longitude' => 82.08, 'is_habitable' => true],
            // Rolwaling — Tamakoshi valley
            ['country' => 'Nepal', 'state' => 'Dolakha', 'city' => 'Singati',  'latitude' => 27.68, 'longitude' => 86.20, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolakha', 'city' => 'Jagat',    'latitude' => 27.70, 'longitude' => 86.23, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolakha', 'city' => 'Simigaon', 'latitude' => 27.73, 'longitude' => 86.25, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Dolakha', 'city' => 'Dongang',  'latitude' => 27.76, 'longitude' => 86.26, 'is_habitable' => true],
            // Saipal — Bajhang valley
            ['country' => 'Nepal', 'state' => 'Bajhang', 'city' => 'Kanda',  'latitude' => 29.72, 'longitude' => 81.28, 'is_habitable' => true],
            ['country' => 'Nepal', 'state' => 'Bajhang', 'city' => 'Nauli',  'latitude' => 29.75, 'longitude' => 81.30, 'is_habitable' => true],
            
            // ============================================================
            // HIDDEN GEMS
            // ============================================================
            ['country' => 'Nepal', 'state' => 'Sindhuli', 'city' => 'Sindhuli', 'latitude' => 27.2789, 'longitude' => 85.9567, 'is_habitable' => true],
        ];

        foreach ($locations as $loc) {
    // Phase 4O: updateOrCreate ensures is_habitable stays correct
    // on re-runs (firstOrCreate skips existing rows)
    Location::updateOrCreate(
        ['city' => $loc['city']],
        $loc
    );
}

        $this->command->info('✅ LocationSeeder completed: ' . count($locations) . ' locations.');
        $this->command->info('   📌 Non‑habitable: Thorong La, Everest Base Camp (is_habitable=false).');
        $this->command->info('   📌 Api Base Camp now habitable (camping/tent stays).');
    }
}