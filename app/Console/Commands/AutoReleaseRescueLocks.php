<?php

namespace App\Console\Commands;

use App\Models\RescueDispatchLock;
use Illuminate\Console\Command;

class AutoReleaseRescueLocks extends Command
{
    protected $signature = 'rescue:release-locks';
    protected $description = 'Auto-release expired rescue dispatch locks';

    public function handle(): int
    {
        $count = RescueDispatchLock::where('expires_at', '<', now())->delete();

        if ($count > 0) {
            $this->info("Released {$count} expired rescue lock(s).");
        }

        return self::SUCCESS;
    }
}