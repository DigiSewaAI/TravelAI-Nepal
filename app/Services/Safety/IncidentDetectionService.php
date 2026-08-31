<?php

namespace App\Services\Safety;

use App\Models\TravelSafetyIncident;
use App\Models\SafetySource;
use App\Models\SafetyIncidentSource;
use App\Jobs\Safety\ProcessIncidentDetectionJob;  // ✅ New use
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IncidentDetectionService
{
    protected $locationResolutionService;

    public function __construct(LocationResolutionService $locationResolutionService)
    {
        $this->locationResolutionService = $locationResolutionService;
    }

    public function processSourceContent(SafetySource $source, array $items): void
    {
        foreach ($items as $item) {
            $this->processSingleItem($source, $item);
        }
    }

    protected function processSingleItem(SafetySource $source, array $item): void
    {
        // 1. Generate content hash
        $hash = md5($item['title'] . ($item['description'] ?? '') . ($item['source_url'] ?? ''));

        // 2. Check if we've already seen this exact content (deduplicate)
        $existingPivot = SafetyIncidentSource::where('content_hash', $hash)
            ->orWhere('source_url', $item['source_url'] ?? '')
            ->first();

        if ($existingPivot) {
            Log::info('Duplicate content skipped', ['hash' => $hash]);
            return;
        }

        // 3. Try to detect if this is a travel-related incident
        $incidentData = $this->extractIncidentData($item);
        if (!$incidentData) {
            Log::info('Not an incident, skipped', ['title' => $item['title']]);
            return;
        }

        // 4. Resolve location
        $locationData = $this->locationResolutionService->resolve($incidentData['location_name'] ?? '');
        if ($locationData) {
            $incidentData['latitude'] = $locationData['latitude'] ?? null;
            $incidentData['longitude'] = $locationData['longitude'] ?? null;
            $incidentData['district'] = $locationData['district'] ?? null;
            $incidentData['province'] = $locationData['province'] ?? null;
        }

        // 5. Check if similar incident already exists (by location + type + time)
        $existingIncident = $this->findSimilarIncident($incidentData);
        if ($existingIncident) {
            // Attach this source as evidence
            $this->attachSourceToIncident($existingIncident, $source, $item, $hash);
            Log::info('Attached to existing incident', ['incident_id' => $existingIncident->id]);
            return;
        }

        // 6. Create new incident
        DB::transaction(function () use ($incidentData, $source, $item, $hash) {
            $incident = TravelSafetyIncident::create([
                'title' => $incidentData['title'],
                'slug' => Str::slug($incidentData['title'] . '-' . uniqid()),
                'incident_type' => $incidentData['incident_type'] ?? 'other',
                'description' => $incidentData['description'] ?? '',
                'severity' => $incidentData['severity'] ?? 'moderate',
                'status' => 'detected',
                'latitude' => $incidentData['latitude'] ?? null,
                'longitude' => $incidentData['longitude'] ?? null,
                'location_name' => $incidentData['location_name'] ?? '',
                'district' => $incidentData['district'] ?? null,
                'province' => $incidentData['province'] ?? null,
                'reported_at' => now(),
                'last_verified_at' => now(),
                'confidence_score' => $source->reliability_score ?? 0.5,
                'official_confirmation' => $source->source_category === 'official',
                'travel_impact' => $this->estimateTravelImpact($incidentData),
                'raw_source_reference' => ['source_id' => $source->id, 'url' => $item['source_url'] ?? ''],
            ]);

            $this->attachSourceToIncident($incident, $source, $item, $hash);

            Log::info('New incident created', ['incident_id' => $incident->id, 'title' => $incident->title]);

            // ✅ Dispatch matching job after incident is saved
            ProcessIncidentDetectionJob::dispatch($incident->id);
        });
    }

    protected function extractIncidentData(array $item): ?array
    {
        $title = $item['title'] ?? '';
        $description = $item['description'] ?? '';

        // Simple keyword detection (generic)
        $keywords = ['flood', 'landslide', 'earthquake', 'avalanche', 'storm', 'heavy rain',
                     'road closure', 'bridge', 'trail', 'accident', 'emergency', 'disaster',
                     'snow', 'glacial', 'wildfire', 'protest', 'strike'];
        $text = strtolower($title . ' ' . $description);
        $matched = false;
        foreach ($keywords as $kw) {
            if (strpos($text, $kw) !== false) {
                $matched = true;
                break;
            }
        }
        if (!$matched) {
            return null;
        }

        // Extract location (simple heuristic)
        $location = $this->extractLocation($text);

        return [
            'title' => $title,
            'description' => $description,
            'incident_type' => $this->detectIncidentType($text),
            'severity' => $this->detectSeverity($text),
            'location_name' => $location,
        ];
    }

    protected function extractLocation(string $text): string
    {
        // Simple regex for place names (Nepal districts/places)
        $places = ['Kathmandu', 'Pokhara', 'Everest', 'Annapurna', 'Langtang', 'Chitwan',
                   'Manang', 'Mustang', 'Dolpa', 'Jomsom', 'Lukla', 'Namche', 'Gokyo',
                   'Rara', 'Janakpur', 'Lumbini', 'Biratnagar', 'Butwal', 'Bharatpur',
                   'Hetauda', 'Nepalgunj', 'Birendranagar', 'Siddharthanagar', 'Damak',
                   'Dharan', 'Itahari', 'Triyuga', 'Bhaktapur', 'Patan', 'Kirtipur',
                   'Baneshwar', 'Swayambhu', 'Pashupati', 'Budhanilkantha'];
        foreach ($places as $place) {
            if (stripos($text, $place) !== false) {
                return $place;
            }
        }
        return '';
    }

    protected function detectIncidentType(string $text): string
    {
        $types = [
            'earthquake' => ['earthquake', 'quake'],
            'flood' => ['flood', 'inundation'],
            'landslide' => ['landslide', 'slip'],
            'avalanche' => ['avalanche', 'snowslide'],
            'storm' => ['storm', 'cyclone', 'typhoon'],
            'heavy_rain' => ['heavy rain', 'rainfall', 'downpour'],
            'road_closure' => ['road closure', 'road blocked', 'highway closed'],
            'trail_closure' => ['trail closed', 'trekking route blocked'],
            'wildfire' => ['wildfire', 'forest fire'],
            'security' => ['protest', 'strike', 'riot', 'bandh'],
        ];
        foreach ($types as $type => $keywords) {
            foreach ($keywords as $kw) {
                if (stripos($text, $kw) !== false) {
                    return $type;
                }
            }
        }
        return 'other';
    }

    protected function detectSeverity(string $text): string
    {
        if (stripos($text, 'critical') !== false || stripos($text, 'emergency') !== false) {
            return 'critical';
        } elseif (stripos($text, 'major') !== false || stripos($text, 'severe') !== false) {
            return 'high';
        } elseif (stripos($text, 'minor') !== false || stripos($text, 'small') !== false) {
            return 'low';
        }
        return 'moderate';
    }

    protected function estimateTravelImpact(array $data): string
    {
        // Simple heuristic
        $type = $data['incident_type'] ?? 'other';
        $severe = ['earthquake', 'avalanche', 'flood', 'landslide', 'road_closure', 'trail_closure'];
        if (in_array($type, $severe)) {
            return 'severe';
        }
        return 'moderate';
    }

    protected function findSimilarIncident(array $data): ?TravelSafetyIncident
    {
        $query = TravelSafetyIncident::where('status', '!=', 'resolved')
            ->where('status', '!=', 'false_positive');

        if (!empty($data['location_name'])) {
            $query->where('location_name', 'LIKE', '%' . $data['location_name'] . '%');
        }
        if (!empty($data['incident_type'])) {
            $query->where('incident_type', $data['incident_type']);
        }

        // Time window: within last 7 days
        $query->where('created_at', '>=', now()->subDays(7));

        return $query->first();
    }

    protected function attachSourceToIncident(TravelSafetyIncident $incident, SafetySource $source, array $item, string $hash): void
    {
        SafetyIncidentSource::updateOrCreate(
            [
                'incident_id' => $incident->id,
                'source_id' => $source->id,
                'source_url' => $item['source_url'] ?? '',
            ],
            [
                'source_title' => $item['title'] ?? '',
                'published_at' => $item['published_at'] ?? now(),
                'retrieved_at' => now(),
                'source_type' => $source->type,
                'source_reliability' => $source->reliability_score ?? 0.5,
                'evidence_text' => $item['description'] ?? '',
                'content_hash' => $hash,
            ]
        );
    }
}