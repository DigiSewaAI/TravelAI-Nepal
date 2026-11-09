<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\Safety\FetchSafetySourcesJob;
use App\Jobs\Safety\VerifyExpiredIncidentsJob;
use App\Jobs\Safety\UpdateSafetyStatusesJob;
use App\Jobs\ExpireSubscriptionsJob;
use App\Jobs\ReleaseStaleAiReservationsJob;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Phase 2: Safety Source Fetch – runs every 5 minutes
Schedule::job(new FetchSafetySourcesJob)->everyFiveMinutes()->withoutOverlapping();

// Phase 4: Verify expired/stale incidents – runs daily
Schedule::job(new VerifyExpiredIncidentsJob)->daily()->withoutOverlapping();

// Phase 4: Update safety statuses – runs every 15 minutes
Schedule::job(new UpdateSafetyStatusesJob)->everyFifteenMinutes()->withoutOverlapping();

// FIX-07: Subscription expiry – runs daily
Schedule::job(new ExpireSubscriptionsJob)->daily()->withoutOverlapping();

// FIX-12: Release stale AI reservations — every 5 minutes
Schedule::job(new ReleaseStaleAiReservationsJob)->everyFiveMinutes()->withoutOverlapping();

// FOREX Phase 3: Daily forex rate refresh at 6 AM Nepal time
Schedule::command('forex:update')
    ->dailyAt('06:00')
    ->timezone('Asia/Kathmandu')
    ->withoutOverlapping();

// Phase 7: SOS auto-escalation – runs every minute
Schedule::command('sos:escalate')->everyMinute()->withoutOverlapping();
Schedule::command('rescue:release-locks')->everyMinute()->withoutOverlapping();
