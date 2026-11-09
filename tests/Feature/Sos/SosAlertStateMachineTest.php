<?php

namespace Tests\Feature\Sos;

use App\Models\Provider;
use App\Models\SosAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SosAlertStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::create([
            'name'     => 'Test User ' . uniqid(),
            'email'    => 'user_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role'     => 'traveler',
        ]);
    }

    private function makeProvider(): Provider
    {
        $user = $this->makeUser();
        return Provider::create([
            'user_id'         => $user->id,
            'name'            => 'Test Provider ' . uniqid(),
            'slug'            => 'test-provider-' . Str::random(8),
            'contact_email'   => 'provider_' . uniqid() . '@example.com',
            'contact_phone'   => '9800000000',
            'address'         => 'Kathmandu',
            'is_active'       => true,
        ]);
    }

    private function makeSos(string $status = 'pending'): SosAlert
    {
        $provider = $this->makeProvider();
        return SosAlert::create([
            'traveler_id' => $this->makeUser()->id,
            'provider_id' => $provider->id,
            'latitude'    => 27.7,
            'longitude'   => 85.3,
            'status'      => $status,
        ]);
    }

    public function test_pending_can_acknowledge(): void
    {
        $sos = $this->makeSos('pending');
        $this->assertTrue($sos->canAcknowledge());
    }

    public function test_acknowledge_transitions_to_acknowledged(): void
    {
        $sos = $this->makeSos('pending');
        $user = $this->makeUser();
        $this->assertTrue($sos->acknowledge($user->id));
        $this->assertEquals('acknowledged', $sos->fresh()->status);
        $this->assertNotNull($sos->fresh()->acknowledged_at);
    }

    public function test_acknowledged_can_respond(): void
    {
        $sos = $this->makeSos('acknowledged');
        $this->assertTrue($sos->canRespond());
    }

    public function test_respond_transitions_to_responding(): void
    {
        $sos = $this->makeSos('acknowledged');
        $user = $this->makeUser();
        $this->assertTrue($sos->respond($user->id));
        $this->assertEquals('responding', $sos->fresh()->status);
    }

    public function test_responding_can_resolve(): void
    {
        $sos = $this->makeSos('responding');
        $this->assertTrue($sos->canResolve());
    }

    public function test_pending_can_also_resolve_legacy_compat(): void
    {
        $sos = $this->makeSos('pending');
        $this->assertTrue($sos->canResolve());
    }

    public function test_resolved_is_terminal(): void
    {
        $sos = $this->makeSos('resolved');
        $this->assertTrue($sos->isTerminal());
        $this->assertFalse($sos->canResolve());
        $this->assertFalse($sos->canAcknowledge());
        $this->assertFalse($sos->canRespond());
    }

    public function test_false_alarm_is_terminal(): void
    {
        $sos = $this->makeSos('false_alarm');
        $this->assertTrue($sos->isTerminal());
        $this->assertFalse($sos->canResolve());
    }

    public function test_action_logged_on_acknowledge(): void
    {
        $sos = $this->makeSos('pending');
        $user = $this->makeUser();
        $sos->acknowledge($user->id);
        $this->assertDatabaseHas('sos_alert_actions', [
            'sos_alert_id' => $sos->id,
            'action'       => 'acknowledged',
            'to_status'    => 'acknowledged',
        ]);
    }

    public function test_false_alarm_requires_reason_and_logs(): void
    {
        $sos = $this->makeSos('pending');
        $user = $this->makeUser();
        $this->assertTrue($sos->markFalseAlarm($user->id, 'Test — accidental trigger'));
        $this->assertEquals('false_alarm', $sos->fresh()->status);
        $this->assertDatabaseHas('sos_alert_actions', [
            'sos_alert_id' => $sos->id,
            'action'       => 'false_alarm',
        ]);
    }
}