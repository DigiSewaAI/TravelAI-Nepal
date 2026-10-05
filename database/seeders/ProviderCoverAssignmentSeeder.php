<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderCoverAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $covers = [
            'providers/covers/0quZy2eVnk5KdcWHXzC4FqcDmS5hTDps4Uo38a8M.jpg',
            'providers/covers/9OFGIbloOJ25rKLXkqFYdTZu63N3JL3gCwnN7DbD.png',
            'providers/covers/gZ1wL0pBYJqBpB6gT9X2i9Zkqp3EK12yEmca1AYn.png',
            'providers/covers/NBEE01ErakAjvJzzwvp5xoltTpJAmir8RfP5ky39.jpg',
            'providers/covers/nro3QuUJlJcxyVuelcsDnKPEk2EbjSkSIxykPDry.jpg',
            'providers/covers/VWBRe6e5IW0GCoeRplVPC6wRPB7bKPfYPc0G819w.png',
            'providers/covers/XlbBxLh9y3Q9kfU9isFWzyfsBY57OjJTV9SMxEST.png',
            'providers/covers/YSBPh9ovei2rFGTi9YEgp6b1m31sjOPr22NvBcbc.png',
        ];

        $logos = [
            'providers/logos/6vZa1pvSTsxQODdli0QLlZJhvp6gbbvuC0Cq3rQp.png',
            'providers/logos/BtO8YQ91Rz8Ltm3dF02Hg8rTC5Iyk0CqU60iE9UR.png',
            'providers/logos/HaMRS1kHPDwaZ5oL4w7R8IkODUS624hTIyU3ykQg.png',
            'providers/logos/PZohXZF8zTvqBm0mnz7n425ZbcEOx0tbkPnbOIrU.png',
            'providers/logos/uS5h3etc49yiwKHv3bqSB9DmpPiofkhStSOGwwXI.png',
            'providers/logos/vUmustwIBcoSuWk6QWB9UwQeiH37d5CtFQBiNiZP.png',
            'providers/logos/XKwrBTlPLvaimqPutKnCoz7XQh83OrsgcykmxRSj.png',
            'providers/logos/z1tCjbuuuXgfZM1uti87V1f2j47oml3mKCH4EIWp.png',
        ];

        // Target: curated + tourism providers (visible on /providers)
        $codes = ['HJO','HTP','HVH','ANS','NCE','PGL','GVH','KHC','HGR','NWE','EVL','MVL'];

        $emails = [
            'himalayan.guides.nepal@travelainepal.com',
            'everest.trailblazers@travelainepal.com',
            'annapurna.eco.treks@travelainepal.com',
            'nepal.heritage.tours@travelainepal.com',
            'mountain.magic.tours@travelainepal.com',
            'hotel.himalayan.view@travelainepal.com',
            'kathmandu.grand.hotel@travelainepal.com',
            'pokhara.lakeside.resort@travelainepal.com',
            'professional.guides.nepal@travelainepal.com',
            'himalayan.transport.service@travelainepal.com',
            'adventure.nepal.activities@travelainepal.com',
            'nepal.homestay.experience@travelainepal.com',
            'nepal.photography.studio@travelainepal.com',
        ];

        $providers = Provider::whereIn('code', $codes)
            ->orWhereHas('user', function ($q) use ($emails) {
                $q->whereIn('email', $emails);
            })
            ->orderBy('id')
            ->get();

        $i = 0;
        foreach ($providers as $p) {
            // Assign cover if empty
            if (empty($p->cover_image)) {
                $p->cover_image = $covers[$i % count($covers)];
            }
            // Assign logo if empty
            if (empty($p->logo_url)) {
                $p->logo_url = $logos[$i % count($logos)];
            }
            $p->save();
            echo "✅ {$p->code} — {$p->name}\n";
            $i++;
        }

        $this->command->info("✅ Assigned: {$i} providers");
        $this->command->info("With cover: " . Provider::whereNotNull('cover_image')->where('cover_image', '!=', '')->count());
        $this->command->info("With logo:  " . Provider::whereNotNull('logo_url')->where('logo_url', '!=', '')->count());
    }
}