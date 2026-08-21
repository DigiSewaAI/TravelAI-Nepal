<?php

namespace App\Services;

use App\Models\RouteSegment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class RouteSegmentService
{
    /**
     * Validate and save a route segment within a transaction.
     */
    public function save(array $data): RouteSegment
    {
        // Rule 1: sequence must be > 0
        if (($data['sequence'] ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'sequence' => 'Sequence must be greater than 0.',
            ]);
        }

        // Rule 2: from_waypoint_id cannot be same as to_waypoint_id
        if ($data['from_waypoint_id'] === $data['to_waypoint_id']) {
            throw ValidationException::withMessages([
                'from_waypoint_id' => 'From and To waypoints cannot be the same.',
            ]);
        }

        // Rule 3: Check for duplicate active sequence in the same route
        $exists = RouteSegment::where('route_id', $data['route_id'])
            ->where('sequence', $data['sequence'])
            ->whereNull('deleted_at') // Only active records
            ->when(isset($data['id']), function ($query) use ($data) {
                // Exclude itself if updating
                return $query->where('id', '!=', $data['id']);
            })
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'sequence' => "Sequence {$data['sequence']} already exists for this route.",
            ]);
        }

        // Save or update
        return RouteSegment::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }
}