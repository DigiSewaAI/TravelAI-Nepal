<?php

namespace Tests\Feature\Sos;

use App\Models\SosAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SosCancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_traveler_can_cancel_own_sos(): void
    {
        $traveler = User::factory()->create(['role' => 'traveler']);
        $sos = SosAlert::create([
            'traveler_id' => $traveler->id,
            'status' => 'sent',
            'latitude' => 27.7,
            'longitude' => 85.3,
            'emergency_level' => 'critical',
        ]);

        $response = $this->actingAs($traveler)->postJson("/api/sos/{$sos->id}/cancel", [
            'reason' => 'Accidental SOS',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
        
        $this->assertEquals('false_alarm', $sos->fresh()->status);
        $this->assertEquals('Accidental SOS', $sos->fresh()->cancel_reason);
    }

    public function test_unauthorized_user_cannot_cancel(): void
    {
        $traveler = User::factory()->create(['role' => 'traveler']);
        $otherUser = User::factory()->create(['role' => 'traveler']);
        $sos = SosAlert::create([
            'traveler_id' => $traveler->id,
            'status' => 'sent',
            'latitude' => 27.7,
            'longitude' => 85.3,
        ]);

        $response = $this->actingAs($otherUser)->postJson("/api/sos/{$sos->id}/cancel", [
            'reason' => 'Malicious cancel',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('sent', $sos->fresh()->status);
    }
}