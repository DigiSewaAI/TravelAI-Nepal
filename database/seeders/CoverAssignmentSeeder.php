<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\Service;
use Illuminate\Database\Seeder;

class CoverAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // R2 uploaded service image filenames
        $files = [
            'services/1x9Ltz6eb0dniXB4jh8wOWLY6JqewW3PQXn7QOWg.jpg',
            'services/34rliH6eml114WU1wW1HQNnGV5D1KVpnNFmkhKof.jpg',
            'services/47BYHVZT1tkjAuODWZyDxsSGNab1whsfCxPec4f1.jpg',
            'services/Blnch670cXTKpYfx0HYgKRrwMpZfpGx7Ncbi9hWx.jpg',
            'services/CRN4DZ6CZfHyERjPIpLB1T6cDeED86UAZPj4SB0N.jpg',
            'services/ChnMJuTpsHyFNsxTj956QWp6lF2fgykbSdSuBJyP.jpg',
            'services/F7cyOh29E4ItIXNLcqjAwZXhUW3Z7Zgpo6fPv1J3.jpg',
            'services/Fr5eiVOLLQ259JdwlAb0Vt0YhNobifxZpdWBCX7R.jpg',
            'services/HsAsMnEvVIIO3afVRhYLe8q71a8lHROIx1KlUKyC.jpg',
            'services/KgsdYtg7hsd5OUt46xReuxPMolqIMUyDdN3Fozsp.jpg',
            'services/KlEuUUI4gvs44ViQl3v4Fdv8crVoV7XLQg7gcvz4.jpg',
            'services/MQlGejiHprFDsTJ6g4mOvraGyeCMO7aMXL7nYfif.jpg',
            'services/McyYsRFjCKzkyYbBWfxdIU3YXRUFaBh42XsHgXxp.jpg',
            'services/VjIqNqeMNI6JWXvJGcCz2RcTNwnh9gohiQy1iW9A.jpg',
            'services/WsHjggGbCtYqKz7JM97q7wXq6jnvzULhQDVIQJee.jpg',
            'services/dnhHNgDpwMNpGpU6tyahl33ChtPcbvx0iPVXd0HL.jpg',
            'services/fQKJI0TnrGEK4CLNgzxMSANst24AYBOFFtnmtQJx.webp',
            'services/iGQX4hT23UEmasdDPFtpeHaUHomv9qppBiB8e2oc.jpg',
            'services/irJLgGDRjvavaTA6Zls9C0fU9udZKQzzosZgo0yv.jpg',
            'services/jPThg75GRpqlRvjZCG5AczSvSFu6MMlg0W5bzj7T.jpg',
            'services/krkJGerxCtzgukcgpBOp11B4wDcnZvPp0iCBWST3.jpg',
            'services/swaMhv6e13JV2zhM0jjlR09iwrr7b9GNgtlKRard.jpg',
            'services/uIYF6bvvAs3eqaP1VmNZ17UuRTePCJ5JlNaun6C1.jpg',
        ];

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

        $pids = Provider::whereIn('code', $codes)
            ->orWhereHas('user', function ($q) use ($emails) {
                $q->whereIn('email', $emails);
            })
            ->pluck('id');

        $svcs = Service::whereIn('provider_id', $pids)
            ->where(function ($q) {
                $q->whereNull('cover_image')->orWhere('cover_image', '');
            })
            ->orderBy('id', 'desc')
            ->get();

        $i = 0;
        foreach ($svcs as $s) {
            $s->cover_image = $files[$i % count($files)];
            $s->save();
            $i++;
        }

        $this->command->info("✅ Assigned: {$i} services");
        $this->command->info("Total with cover: " . Service::whereNotNull('cover_image')->where('cover_image', '!=', '')->count());
    }
}