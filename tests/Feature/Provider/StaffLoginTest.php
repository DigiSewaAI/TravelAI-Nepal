<?php

namespace Tests\Feature\Provider;

use App\Models\Provider;
use App\Models\ProviderStaff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffLoginTest extends TestCase
{
    use RefreshDatabase;

    private function makeOwner(): User
    {
        return User::create([
            'name'     => 'Owner User',
            'email'    => 'owner-' . uniqid() . '@test.local',
            'password' => Hash::make('password123'),
            'role'     => 'provider_owner',
        ]);
    }

    private function makeProvider(User $owner): Provider
    {
        return Provider::create([
            'user_id'             => $owner->id,
            'name'                => 'Test Provider',
            'slug'                => 'test-provider-' . uniqid(),
            'contact_email'       => 'provider@test.local',
            'verification_status' => 'verified',
            'is_active'           => true,
            'max_staff'           => 10,
        ]);
    }

    private function makeStaff(): User
    {
        return User::create([
            'name'     => 'Staff User',
            'email'    => 'staff-' . uniqid() . '@test.local',
            'password' => Hash::make('password123'),
            'role'     => 'staff',
        ]);
    }

    public function test_t1_staff_with_link_can_resolve_provider(): void
    {
        $owner    = $this->makeOwner();
        $provider = $this->makeProvider($owner);
        $staff    = $this->makeStaff();

        ProviderStaff::create([
            'user_id'     => $staff->id,
            'provider_id' => $provider->id,
            'role'        => 'guide',
            'permissions' => [],
        ]);

        $staff->refresh();
        $resolved = $staff->getCurrentProvider();

        $this->assertNotNull($resolved);
        $this->assertSame($provider->id, $resolved->id);
    }

    public function test_t2_staff_without_link_returns_null(): void
    {
        $staff = $this->makeStaff();
        $this->assertNull($staff->getCurrentProvider());
    }

    public function test_t3_provider_owner_still_resolves_own_provider(): void
    {
        $owner    = $this->makeOwner();
        $provider = $this->makeProvider($owner);

        $owner->refresh();
        $resolved = $owner->getCurrentProvider();

        $this->assertNotNull($resolved);
        $this->assertSame($provider->id, $resolved->id);
    }

    public function test_t4_staff_can_login_and_redirects_to_provider_dashboard(): void
    {
        $owner    = $this->makeOwner();
        $provider = $this->makeProvider($owner);
        $staff    = $this->makeStaff();

        ProviderStaff::create([
            'user_id'     => $staff->id,
            'provider_id' => $provider->id,
            'role'        => 'guide',
            'permissions' => [],
        ]);

        $response = $this->post('/login', [
            'email'    => $staff->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('provider.dashboard'));
        $this->assertAuthenticatedAs($staff);
    }
}