<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\Safety\FetchSafetySourcesJob;
use App\Jobs\Safety\VerifyExpiredIncidentsJob;   // ✅ New Phase 4 Job
use App\Jobs\Safety\UpdateSafetyStatusesJob;      // ✅ New Phase 4 Job
use App\Jobs\ExpireSubscriptionsJob;              // FIX-07
use App\Jobs\CleanupStripeWebhookEventsJob;       // FIX-08
use App\Jobs\ReleaseStaleAiReservationsJob;       // FIX-12

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Phase 2: Safety Source Fetch – runs every 5 minutes
Schedule::job(new FetchSafetySourcesJob)->everyFiveMinutes()->withoutOverlapping();

// Phase 4: Verify expired/stale incidents – runs daily
Schedule::job(new VerifyExpiredIncidentsJob)->daily()->withoutOverlapping();

// Phase 4: Update safety statuses for all entities – runs every 15 minutes
Schedule::job(new UpdateSafetyStatusesJob)->everyFifteenMinutes()->withoutOverlapping();

// FIX-07: Subscription expiry – runs daily
Schedule::job(new ExpireSubscriptionsJob)->daily()->withoutOverlapping();

// FIX-08: Stripe webhook event retention cleanup – runs daily
Schedule::job(new CleanupStripeWebhookEventsJob)->daily()->withoutOverlapping();

// FIX-12: Release stale AI reservations — every 5 minutes
Schedule::job(new ReleaseStaleAiReservationsJob)->everyFiveMinutes()->withoutOverlapping();