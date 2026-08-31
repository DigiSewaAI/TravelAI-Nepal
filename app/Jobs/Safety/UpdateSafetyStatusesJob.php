<?php

namespace App\Jobs\Safety;

use App\Models\Waypoint;
use App\Models\Route;
use App\Models\Trek;
use App\Models\Location;
use App\Services\Safety\SafetyStatusService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateSafetyStatusesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $entityType;
    protected $entityId;

    public function __construct(?string $entityType = null, ?int $entityId = null)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
    }

    public function handle(SafetyStatusService $statusService): void
    {
        // If specific entity provided, update only that
        if ($this->entityType && $this->entityId) {
            $this->updateSpecificEntity($statusService);
            return;
        }

        // Otherwise update all
        $this->updateAllEntities($statusService);
    }

    protected function updateSpecificEntity(SafetyStatusService $statusService): void
    {
        $entity = $this->findEntity($this->entityType, $this->entityId);
        if ($entity) {
            $statusService->refreshEntityStatus($entity);
        }
    }

    protected function updateAllEntities(SafetyStatusService $statusService): void
    {
        Log::info('Starting safety status update for all entities');

        // Update Waypoints
        Waypoint::chunk(100, function ($waypoints) use ($statusService) {
            foreach ($waypoints as $waypoint) {
                $statusService->refreshEntityStatus($waypoint);
            }
        });

        // Update Routes
        Route::chunk(100, function ($routes) use ($statusService) {
            foreach ($routes as $route) {
                $statusService->refreshEntityStatus($route);
            }
        });

        // Update Treks
        Trek::chunk(100, function ($treks) use ($statusService) {
            foreach ($treks as $trek) {
                $statusService->refreshEntityStatus($trek);
            }
        });

        // Update Locations
        Location::chunk(100, function ($locations) use ($statusService) {
            foreach ($locations as $location) {
                $statusService->refreshEntityStatus($location);
            }
        });

        Log::info('Safety status update completed for all entities');
    }

    protected function findEntity(string $type, int $id)
    {
        return match ($type) {
            'waypoint' => Waypoint::find($id),
            'route' => Route::find($id),
            'trek' => Trek::find($id),
            'location' => Location::find($id),
            default => null,
        };
    }
}