<?php

namespace App\Services;

use App\Models\RouteCost;
use Illuminate\Validation\ValidationException;

class RouteCostService
{
    /**
     * Validate and save a route cost.
     */
    public function save(array $data): RouteCost
    {
        // Prevent overlapping effective periods for same route & type
        $query = RouteCost::where('route_id', $data['route_id'])
            ->where('type', $data['type'])
            ->whereNull('deleted_at');

        // Exclude itself when updating
        if (isset($data['id'])) {
            $query->where('id', '!=', $data['id']);
        }

        $overlap = $query->where(function ($q) use ($data) {
            $q->where(function ($q2) use ($data) {
                // Existing starts before or during the new period
                $q2->where('effective_from', '<=', $data['effective_until']);
            })->where(function ($q3) use ($data) {
                // Existing ends after or during the new period
                $q3->whereNull('effective_until')
                  ->orWhere('effective_until', '>=', $data['effective_from']);
            });
        })->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'effective_from' => 'This cost period overlaps with an existing active cost of the same type.',
            ]);
        }

        return RouteCost::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }
}