<?php

namespace Tests\Feature\Safety;

use App\Models\SafetySource;
use App\Models\TravelSafetyIncident;
use App\Models\Waypoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase1Test extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function safety_source_model_can_be_created()
    {
        $source = SafetySource::create([
            'name' => 'Test RSS',
            'type' => 'rss',
            'feed_url' => 'http://example.com/feed',
            'source_category' => 'news',
            'reliability_score' => 0.8,
            'enabled' => true,
            'fetch_interval' => 30,
        ]);

        $this->assertDatabaseHas('safety_sources', ['name' => 'Test RSS']);
        $this->assertEquals(0.8, $source->reliability_score);
    }

    /** @test */
    public function incident_can_be_created_with_sources()
    {
        // ✅ Fix: provide all required fields for SafetySource
        $source = SafetySource::create([
            'name' => 'Test Source',
            'type' => 'rss',
            'feed_url' => 'http://example.com/feed',
            'source_category' => 'news',
            'reliability_score' => 0.8,
            'enabled' => true,
            'fetch_interval' => 30,
        ]);

        $incident = TravelSafetyIncident::create([
            'title' => 'Landslide near Manang',
            'slug' => 'landslide-manang',
            'incident_type' => 'landslide',
            'severity' => 'high',
            'status' => 'detected',
            'latitude' => 28.66,
            'longitude' => 84.01,
            'district' => 'Manang',
            'province' => 'Gandaki',
            'reported_at' => now(),
        ]);

        $incident->sources()->attach($source, [
            'source_url' => 'http://example.com/news',
            'source_reliability' => 0.9,
            'evidence_text' => 'Landslide reported.',
            'content_hash' => 'abc123',
        ]);

        $this->assertCount(1, $incident->sources);
        $this->assertEquals('landslide', $incident->incident_type);
    }

    /** @test */
    public function waypoint_gets_safety_status_via_trait()
    {
        $waypoint = Waypoint::create([
            'name' => 'Test Checkpoint',
            'slug' => 'test-checkpoint',
            'type' => 'checkpoint',
            'latitude' => 28.0,
            'longitude' => 84.0,
        ]);

        // Initially unknown
        $this->assertEquals('unknown', $waypoint->safety_status);

        // Create incident and link
        $incident = TravelSafetyIncident::create([
            'title' => 'Flood',
            'slug' => 'flood-test',
            'incident_type' => 'flood',
            'severity' => 'high',
            'status' => 'active',
            'latitude' => 28.0,
            'longitude' => 84.0,
            'reported_at' => now(),
        ]);

        $incident->waypoints()->attach($waypoint, [
            'distance' => 100,
            'match_type' => 'exact',
            'confidence' => 0.9,
        ]);

        // Refresh safety status
        $waypoint->refreshSafetyStatus();

        $this->assertEquals('high_risk', $waypoint->fresh()->safety_status);
    }

    /** @test */
    public function risk_scoring_service_works()
    {
        $service = app(\App\Services\Safety\RiskScoringService::class);
        $incident = TravelSafetyIncident::create([
            'title' => 'Test',
            'slug' => 'test',
            'incident_type' => 'flood',
            'severity' => 'high',
            'status' => 'active',
            'latitude' => 28.0,
            'longitude' => 84.0,
            'official_confirmation' => true,
            'travel_impact' => 'moderate',
            'reported_at' => now(),
        ]);

        $waypoint = Waypoint::create([
            'name' => 'Test WP',
            'slug' => 'test-wp',
            'type' => 'checkpoint',
            'latitude' => 28.001,
            'longitude' => 84.001,
        ]);

        $score = $service->calculateScore($incident, $waypoint);
        $status = $service->scoreToStatus($score);

        $this->assertIsFloat($score);
        $this->assertGreaterThan(0, $score);
        $this->assertLessThanOrEqual(100, $score);
        $this->assertContains($status, ['normal', 'caution', 'high_risk', 'avoid']);
    }
}