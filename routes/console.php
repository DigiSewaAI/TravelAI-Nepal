<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\Safety\FetchSafetySourcesJob;
use App\Jobs\Safety\VerifyExpiredIncidentsJob;   // ✅ New Phase 4 Job
use App\Jobs\Safety\UpdateSafetyStatusesJob;      // ✅ New Phase 4 Job

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
Schedule::job(new VerifyExpiredIncidentsJob)->daily();

// Phase 4: Update safety statuses for all entities – runs every 15 minutes
Schedule::job(new UpdateSafetyStatusesJob)->everyFifteenMinutes();