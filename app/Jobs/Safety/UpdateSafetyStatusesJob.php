<?php

namespace App\Jobs\Safety;

use App\Models\Waypoint;
use App\Models\Route;
use App\Models\Location;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class UpdateSafetyStatusesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Update all Waypoints
        Waypoint::chunk(100, function ($waypoints) {
            foreach ($waypoints as $waypoint) {
                try {
                    $waypoint->refreshSafetyStatus();
                } catch (\Exception $e) {
                    Log::error('Failed to update waypoint safety status', [
                        'waypoint_id' => $waypoint->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        // Update all Routes
        Route::chunk(100, function ($routes) {
            foreach ($routes as $route) {
                try {
                    $route->refreshSafetyStatus();
                } catch (\Exception $e) {
                    Log::error('Failed to update route safety status', [
                        'route_id' => $route->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        // Update all Locations (if table exists)
        try {
            Location::chunk(100, function ($locations) {
                foreach ($locations as $location) {
                    try {
                        if (method_exists($location, 'refreshSafetyStatus')) {
                            $location->refreshSafetyStatus();
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to update location safety status', [
                            'location_id' => $location->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });
        } catch (QueryException $e) {
            // Location table might not have safety_status columns yet
            Log::warning('Location safety status update skipped', [
                'error' => $e->getMessage()
            ]);
        }

        // Note: Trek updates are skipped because 'treks' table doesn't exist yet
        // Will be added when Trek module is ready
    }
}