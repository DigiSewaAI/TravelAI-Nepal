<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderType;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProductionFixSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('ProductionFixSeeder: starting...');

        // STEP 1 - Pre-step email renames (Trap 2)
        $emailRenames = [
            'demo.hotel@travelainepal.com'      => 'book@himalayanviewhotel.com',
            'demo.activity@travelainepal.com'   => 'info@adventurenepalsports.com',
            'demo.experience@travelainepal.com' => 'hello@nepalcultural.com',
            'demo.resort@travelainepal.com'     => 'book@pokharagrandlakeview.com',
            'demo.homestay@travelainepal.com'   => 'stay@gurungvillagehomestay.com',
        ];
        foreach ($emailRenames as $old => $new) {
            User::where('email', $old)->update(['email' => $new]);
        }
        $this->command->info('  [1/8] Emails renamed');

        // STEP 2 - Pre-step code renames (Trap 3)
        $codeRenames = [
            'DMH'  => 'HVH',
            'DMA'  => 'ANS',
            'DME'  => 'NCE',
            'DMR'  => 'PGL',
            'DMH2' => 'GVH',
        ];
        foreach ($codeRenames as $old => $new) {
            Provider::where('code', $old)->update(['code' => $new]);
        }
        $this->command->info('  [2/8] Codes renamed');

        // STEP 3 - Rename DMR (Pokhara Lakeside Resort) - id=477
        Provider::where('code', 'PGL')->update([
            'name'          => 'Pokhara Grand Lakeview',
            'slug'          => 'pokhara-grand-lakeview',
            'contact_email' => 'book@pokharagrandlakeview.com',
        ]);
        $this->command->info('  [3/8] PGL renamed');

        // STEP 4 - Email domain fix (@travelai.com -> @travelainepal.com)
        foreach (DB::table('users')->where('email', 'LIKE', '%@travelai.com')->get() as $u) {
            $newEmail = str_replace('@travelai.com', '@travelainepal.com', $u->email);
            DB::table('users')->where('id', $u->id)->update(['email' => $newEmail]);
        }
        DB::table('providers')->where('contact_email', 'LIKE', '%@travelai.com')
            ->update(['contact_email' => DB::raw("REPLACE(contact_email, '@travelai.com', '@travelainepal.com')")]);
        $this->command->info('  [4/8] Domain fixed');

        // STEP 5 - Update demo provider emails (474-478)
        $demoUpdates = [
            'HVH' => ['email' => 'book@himalayanviewhotel.com',      'name' => 'Bikash Thapa'],
            'ANS' => ['email' => 'info@adventurenepalsports.com',    'name' => 'Pemba Sherpa'],
            'NCE' => ['email' => 'hello@nepalcultural.com',           'name' => 'Sunita Gurung'],
            'PGL' => ['email' => 'book@pokharagrandlakeview.com',    'name' => 'Rajesh Adhikari'],
            'GVH' => ['email' => 'stay@gurungvillagehomestay.com',   'name' => 'Kamala Gurung'],
        ];
        foreach ($demoUpdates as $code => $data) {
            $provider = Provider::where('code', $code)->first();
            if ($provider && $provider->user_id) {
                User::where('id', $provider->user_id)->update([
                    'email' => $data['email'],
                    'name'  => $data['name'],
                ]);
                $provider->update(['contact_email' => $data['email']]);
            }
        }
        $this->command->info('  [5/8] Demo emails updated');

        // STEP 6 - Create 3 dedicated providers (KHC, HGR, NWE)
        $dedicated = [
            [
                'code'    => 'KHC',
                'name'    => 'Kathmandu Handicrafts Co.',
                'slug'    => 'kathmandu-handicrafts-co',
                'email'   => 'info@ktmhandicrafts.com',
                'owner'   => 'Ram Bahadur Shrestha',
                'phone'   => '+977-1-5341234',
                'address' => 'Thamel Marg, Kathmandu 44600, Nepal',
                'desc'    => 'Authentic Nepali handicrafts, wooden murtis, and Pashmina textiles.',
                'type'    => 'shop-owner',
            ],
            [
                'code'    => 'HGR',
                'name'    => 'Himalayan Gear Rentals',
                'slug'    => 'himalayan-gear-rentals',
                'email'   => 'rent@himalayangear.com',
                'owner'   => 'Ang Dorje Sherpa',
                'phone'   => '+977-1-5342345',
                'address' => 'Lakeside Marg, Pokhara 33700, Nepal',
                'desc'    => 'Quality trekking and expedition gear rentals for Himalayan adventures.',
                'type'    => 'rental-provider',
            ],
            [
                'code'    => 'NWE',
                'name'    => 'Nepal Wholesale Exporters',
                'slug'    => 'nepal-wholesale-exporters',
                'email'   => 'bulk@nepalwholesale.com',
                'owner'   => 'Sita Gurung',
                'phone'   => '+977-1-5343456',
                'address' => 'Tripureshwor, Kathmandu 44600, Nepal',
                'desc'    => 'Bulk Nepali handicrafts, Ilam tea, and textiles for global retailers.',
                'type'    => 'wholesale-provider',
            ],
        ];
        foreach ($dedicated as $d) {
            $owner = User::updateOrCreate(
                ['email' => $d['email']],
                [
                    'name'              => $d['owner'],
                    'password'          => Hash::make('Himalayan@1980'),
                    'role'              => 'provider_owner',
                    'email_verified_at' => now(),
                ]
            );
            $prov = Provider::updateOrCreate(
                ['code' => $d['code']],
                [
                    'user_id'             => $owner->id,
                    'name'                => $d['name'],
                    'slug'                => $d['slug'],
                    'contact_email'       => $d['email'],
                    'contact_phone'       => $d['phone'],
                    'address'             => $d['address'],
                    'description'         => $d['desc'],
                    'is_active'           => true,
                    'verification_status' => 'verified',
                ]
            );
            $typeId = ProviderType::where('slug', $d['type'])->value('id');
            if ($typeId) {
                $prov->types()->syncWithoutDetaching([$typeId]);
            }
        }
        $this->command->info('  [6/8] Dedicated providers created');

        // STEP 7 - Create 2 lodges (EVL, MVL) + services
        $lodges = [
            [
                'code'    => 'EVL',
                'name'    => 'Everest View Lodge',
                'slug'    => 'everest-view-lodge',
                'email'   => 'book@everestviewlodge.com',
                'owner'   => 'Tenzing Sherpa',
                'phone'   => '+977-38-540123',
                'address' => 'Namche Bazaar, Solukhumbu, Nepal',
                'desc'    => 'Mountain lodge with panoramic views of Everest range.',
                'service' => [
                    'name'  => 'Deluxe Room with Mountain View',
                    'slug'  => 'evl-deluxe-mountain-view',
                    'desc'  => 'Cozy deluxe room with panoramic views of Everest.',
                    'price' => 3500,
                ],
            ],
            [
                'code'    => 'MVL',
                'name'    => 'Mountain View Lodge Pokhara',
                'slug'    => 'mountain-view-lodge-pokhara',
                'email'   => 'stay@mountainviewlodge.com',
                'owner'   => 'Hari Prasad Sharma',
                'phone'   => '+977-61-462345',
                'address' => 'Sarangkot Road, Pokhara 33700, Nepal',
                'desc'    => 'Cozy lodge overlooking Phewa Lake and Annapurna range.',
                'service' => [
                    'name'  => 'Standard Room (Lake View)',
                    'slug'  => 'mvl-standard-room-lake-view',
                    'desc'  => 'Standard room with lake and mountain views.',
                    'price' => 2200,
                ],
            ],
        ];
        $lodgeCategoryId = ServiceCategory::where('slug', 'lodge')->value('id');
        foreach ($lodges as $l) {
            $owner = User::updateOrCreate(
                ['email' => $l['email']],
                [
                    'name'              => $l['owner'],
                    'password'          => Hash::make('Himalayan@1980'),
                    'role'              => 'provider_owner',
                    'email_verified_at' => now(),
                ]
            );
            $prov = Provider::updateOrCreate(
                ['code' => $l['code']],
                [
                    'user_id'             => $owner->id,
                    'name'                => $l['name'],
                    'slug'                => $l['slug'],
                    'contact_email'       => $l['email'],
                    'contact_phone'       => $l['phone'],
                    'address'             => $l['address'],
                    'description'         => $l['desc'],
                    'is_active'           => true,
                    'verification_status' => 'verified',
                ]
            );
            $typeId = ProviderType::where('slug', 'lodge')->value('id');
            if ($typeId) {
                $prov->types()->syncWithoutDetaching([$typeId]);
            }
            Service::updateOrCreate(
                ['slug' => $l['service']['slug']],
                [
                    'provider_id'         => $prov->id,
                    'service_category_id' => $lodgeCategoryId,
                    'name'                => $l['service']['name'],
                    'description'         => $l['service']['desc'],
                    'price'               => $l['service']['price'],
                    'currency'            => 'NPR',
                    'status'              => 'active',
                    'itinerary_status'    => 'published',
                ]
            );
        }
        $this->command->info('  [7/8] Lodges created');

        // STEP 8 - Password reset (Owner directive - no guard)
        DB::table('users')->update([
            'password' => Hash::make('Himalayan@1980'),
        ]);
        $this->command->info('  [8/8] All passwords reset');

        $this->command->info('ProductionFixSeeder: DONE');
    }
}