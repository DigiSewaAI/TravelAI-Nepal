<?php

namespace Tests\Feature\Subscription;

use App\Models\Plan;
use App\Models\Provider;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanChangeRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    private function makeProvider(): array
    {
        $user = User::factory()->create();

        $provider = Provider::create([
            'user_id'             => $user->id,
            'name'                => 'Race Test Provider',
            'slug'                => 'race-test-provider-' . uniqid(),
            'verification_status' => 'verified',
            'is_active'           => true,
        ]);

        $user->refresh();
        return [$user, $provider];
    }

    private function makePlan(string $slug, string $name, float $price): Plan
    {
        return Plan::firstOrCreate(
            ['slug' => $slug],
            [
                'name'             => $name,
                'price_monthly'    => $price,
                'price_yearly'     => $price * 10,
                'requires_contact' => false,
                'features'         => [],
                'limits'           => [],
            ]
        );
    }

    /** @test */
    public function upgrading_in_production_does_not_cancel_current_subscription(): void
    {
        // Debug env
        fwrite(STDERR, "\n[DEBUG] APP_ENV in test: " . app()->environment() . "\n");
        fwrite(STDERR, "[DEBUG] isLocal: " . (app()->environment('local') ? 'true' : 'false') . "\n");

        [$user, $provider] = $this->makeProvider();
        $business = $this->makePlan('business', 'Business', 11999);
        $pro      = $this->makePlan('professional', 'Professional', 4499);

        // Sanity: plan state
        $this->assertFalse($business->isFree(), 'Business isFree');
        $this->assertFalse($pro->isFree(), 'Professional isFree');
        $this->assertFalse($pro->isContactOnly(), 'Professional isContactOnly');

        $businessSub = Subscription::create([
            'provider_id'      => $provider->id,
            'plan_id'          => $business->id,
            'status'           => 'active',
            'billing_interval' => 'monthly',
            'start_date'       => now(),
            'end_date'         => now()->addMonth(),
        ]);

        $this->assertNotNull($user->fresh()->ownProvider(), 'ownProvider null');

        $response = $this->actingAs($user)->post(
            route('provider.subscriptions.upgrade'),
            ['plan_id' => $pro->id, 'billing_interval' => 'monthly']
        );

        fwrite(STDERR, "[DEBUG] Response status: " . $response->status() . "\n");
        fwrite(STDERR, "[DEBUG] Sub statuses: " .
            Subscription::where('provider_id', $provider->id)
                ->pluck('status')->implode(', ') . "\n");

        $businessSub->refresh();
        $this->assertSame(
            'active',
            $businessSub->status,
            'RACE FIX FAILED: current subscription was cancelled.'
        );

        $pendingPro = Subscription::where('provider_id', $provider->id)
            ->where('plan_id', $pro->id)
            ->where('status', 'pending')
            ->first();

        $this->assertNotNull(
            $pendingPro,
            'Pending Pro not created. Subs: ' .
            Subscription::where('provider_id', $provider->id)
                ->get(['id', 'plan_id', 'status'])
                ->map(fn($s) => "#{$s->id}/plan{$s->plan_id}/{$s->status}")
                ->implode(' | ')
        );
    }

    /** @test */
    public function upgrade_cleans_up_old_pending_subscriptions(): void
    {
        [$user, $provider] = $this->makeProvider();
        $business = $this->makePlan('business', 'Business', 11999);
        $pro      = $this->makePlan('professional', 'Professional', 4499);

        $stale = Subscription::create([
            'provider_id'      => $provider->id,
            'plan_id'          => $business->id,
            'status'           => 'pending',
            'billing_interval' => 'monthly',
            'start_date'       => now(),
            'end_date'         => now()->addMonth(),
        ]);

        $this->actingAs($user)->post(
            route('provider.subscriptions.upgrade'),
            ['plan_id' => $pro->id]
        );

        $stale->refresh();
        $this->assertSame('cancelled', $stale->status, 'Stale not cancelled.');
    }
}