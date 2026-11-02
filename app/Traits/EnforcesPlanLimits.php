<?php

namespace App\Traits;

use App\Models\Provider;
use Illuminate\Http\RedirectResponse;

trait EnforcesPlanLimits
{
    /**
     * Check plan limit before creating a resource.
     *
     * @param Provider $provider
     * @param string $limitAttribute  Accessor name, e.g. 'max_rooms'
     * @param string $modelClass      Fully-qualified model class
     * @param string $modelLabel      Human label ("rooms", "vehicles", ...)
     * @return RedirectResponse|null  Null if OK; redirect if limit reached
     */
    protected function checkPlanLimit(
        Provider $provider,
        string $limitAttribute,
        string $modelClass,
        string $modelLabel
    ): ?RedirectResponse {
        $max = $provider->{$limitAttribute} ?? null;

        if ($max === -1 || $max === null) {
            return null; // unlimited or no limit set
        }

        $current = $modelClass::where('provider_id', $provider->id)->count();

        if ($current >= (int) $max) {
            return back()
                ->withErrors(['limit' => "Plan limit reached: max {$max} {$modelLabel}. Please upgrade your plan."])
                ->withInput();
        }

        return null;
    }
}