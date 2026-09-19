<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    use AuthorizesRequests;

    public function index()
{
    $provider = Auth::user()->ownProvider();
    $services = $provider
        ? $provider->services()->withCount('itineraryDays')->get()
        : collect();
    return view('provider.services.index', compact('services'));
}

    public function create()
    {
        $categories = ServiceCategory::all();
        return view('provider.services.create', compact('categories'));
    }

        public function store(Request $request)
    {
        // FIX-15 D4: explicit create authorization
        $this->authorize('create', Service::class);

        $provider = Auth::user()->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'price'               => 'nullable|numeric|min:0',
            'currency'            => 'required|in:USD,NPR',
            'description'         => 'nullable|string',
            'cover_image'         => 'nullable|image|max:2048',
            'status'              => 'nullable|in:active,inactive',
        ]);

        // Fast pre-check (non-atomic) — avoid file upload if clearly over limit
        $quickMax = $provider->max_listings; // FIX-15 D5: uses helper
        if ($quickMax !== -1 && $quickMax !== null) {
            $quickCount = Service::where('provider_id', $provider->id)
                ->where('status', 'active')
                ->count();

            if ($quickCount >= $quickMax) {
                return $this->limitReachedResponse($quickMax);
            }
        }

        // File upload (outside transaction)
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('services', 'public');
            $validated['cover_image'] = $path;
        }

        $validated['provider_id'] = $provider->id;
        $validated['slug']        = Str::slug($validated['name']) . '-' . Str::random(6);
        $validated['status']      = $validated['status'] ?? 'active';

        $uploadedFile = $validated['cover_image'] ?? null;
        $limitReached = false;
        $effectiveMax = 0;

        try {
            DB::transaction(function () use ($provider, $validated, &$limitReached, &$effectiveMax) {
                // FIX-15 D1: atomic row lock on provider to serialize concurrent creations
                $lockedProvider = Provider::where('id', $provider->id)
                    ->lockForUpdate()
                    ->first();

                $max = $lockedProvider->max_listings;

                // Enterprise unlimited: -1 means no limit
                if ($max === -1 || $max === null) {
                    $max = PHP_INT_MAX;
                }

                $effectiveMax = $max;

                // FIX-15 D2: active-only counting (unchanged behavior, now documented)
                $activeCount = Service::where('provider_id', $lockedProvider->id)
                    ->where('status', 'active')
                    ->count();

                if ($activeCount >= $max) {
                    $limitReached = true;
                    return;
                }

                Service::create($validated);
            });
        } catch (\Throwable $e) {
            if ($uploadedFile) {
                \Storage::disk('public')->delete($uploadedFile);
            }

            Log::error('Service creation failed', [
                'provider_id' => $provider->id,
                'error_class' => get_class($e),
            ]);

            return back()
                ->withErrors(['error' => 'Could not create service. Please try again.'])
                ->withInput();
        }

        if ($limitReached) {
            if ($uploadedFile) {
                \Storage::disk('public')->delete($uploadedFile);
            }

            return $this->limitReachedResponse($effectiveMax);
        }

        return redirect()
            ->route('provider.services.index')
            ->with('success', 'Service created successfully.');
    }

    /**
     * FIX-15: Shared limit-reached response (preserves original UX message).
     */
    protected function limitReachedResponse(int $maxServices)
    {
        return back()
            ->withErrors([
                'limit' => "You have reached the maximum limit of {$maxServices} services for your current plan. Please <a href='" . route('provider.subscriptions.index') . "' class='text-blue-600 underline font-semibold hover:text-blue-800'>upgrade to the Professional or Business plan</a> to add more services."
            ])
            ->with('error', "Service limit reached! Max {$maxServices} services allowed.")
            ->withInput();
    }

    public function edit(Service $service)
    {
        $this->authorize('update', $service);
        $categories = ServiceCategory::all();
        return view('provider.services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
{
    $this->authorize('update', $service);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'service_category_id' => 'required|exists:service_categories,id',
        'price' => 'nullable|numeric|min:0',
        'currency' => 'required|in:USD,NPR',  // ✅ Added
        'description' => 'nullable|string',
        'cover_image' => 'nullable|image|max:2048',
        'status' => 'nullable|in:active,inactive',
    ]);

    if ($request->hasFile('cover_image')) {
        if ($service->cover_image) {
            \Storage::disk('public')->delete($service->cover_image);
        }
        $path = $request->file('cover_image')->store('services', 'public');
        $validated['cover_image'] = $path;
    }

    $service->update($validated);

    return redirect()->route('provider.services.index')->with('success', 'Service updated successfully.');
}

    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);

        if ($service->cover_image) {
            \Storage::disk('public')->delete($service->cover_image);
        }

        $service->delete();

        return redirect()->route('provider.services.index')->with('success', 'Service deleted successfully.');
    }
}