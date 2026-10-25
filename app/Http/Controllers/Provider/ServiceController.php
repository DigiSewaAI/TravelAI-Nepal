<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use App\Models\Provider;
use App\Models\TrekDetail;
use App\Models\TourDetail;
use App\Models\HotelDetail;
use App\Models\ActivityDetail;
use App\Models\ExperienceDetail;
use App\Models\TransportDetail;
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
        $provider = Auth::user()->ownProvider();
        $providerType = $provider?->types()->first();

        // DYNAMIC-UI: Product Provider (Wholesale, Shop, Rental) लाई Service बनाउन नदिने
        $productSlugs = ['shop-owner', 'rental-provider', 'wholesale-provider'];
        if ($providerType && in_array($providerType->slug, $productSlugs)) {
            return redirect()->route('provider.products.create')
                ->with('info', 'Please create a Product instead of a Service.');
        }

        // Product Category (shop, rental, wholesale) लाई Service को फर्मबाट हटाउने
        $productCategorySlugs = ['shop', 'rental', 'wholesale'];

        if ($providerType && $providerType->serviceCategories->isNotEmpty()) {
            $allowedCategories = $providerType->serviceCategories->reject(function ($cat) use ($productCategorySlugs) {
                return in_array($cat->slug, $productCategorySlugs);
            });
        } else {
            $allowedCategories = ServiceCategory::whereNotIn('slug', $productCategorySlugs)->get();
        }

        $categories = $allowedCategories;

        $locations = Location::whereNotNull('city')->where('city', '!=', '')->orderBy('city')->get(['id', 'city']);
        return view('provider.services.create', compact('categories', 'allowedCategories', 'providerType', 'locations'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Service::class);

        $provider = Auth::user()->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        // DYNAMIC-UI: Product Provider ले सिधै URL बाट पनि Service बनाउन नपाउने
        $providerTypeSlug = $provider->types()->first()?->slug;
        if (in_array($providerTypeSlug, ['shop-owner', 'rental-provider', 'wholesale-provider'])) {
            abort(403, 'Product providers cannot create services.');
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

        $providerType = $provider->types()->first();
        $allowedCategories = $providerType ? $providerType->serviceCategories : collect();
        $productCategorySlugs = ['shop', 'rental', 'wholesale'];

        if ($allowedCategories->isNotEmpty()) {
            $allowedIds = $allowedCategories->reject(function($cat) use ($productCategorySlugs) {
                return in_array($cat->slug, $productCategorySlugs);
            })->pluck('id')->all();
            
            if (!in_array((int) $validated['service_category_id'], $allowedIds, true)) {
                abort(403, __('messages.category_not_allowed'));
            }
        }

        $quickMax = $provider->max_listings;
        if ($quickMax !== -1 && $quickMax !== null) {
            $quickCount = Service::where('provider_id', $provider->id)
                ->where('status', 'active')
                ->count();

            if ($quickCount >= $quickMax) {
                return $this->limitReachedResponse($quickMax);
            }
        }

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('services', 'public');
            $validated['cover_image'] = $path;
        }

        $category = ServiceCategory::find($validated['service_category_id']);
        $slug     = $category ? $category->slug : '';
        $detailData = $this->validateDetailFields($request, $slug);

        $validated['provider_id'] = $provider->id;
        $validated['slug']        = Str::slug($validated['name']) . '-' . Str::random(6);
        $validated['status']      = $validated['status'] ?? 'active';

        $uploadedFile = $validated['cover_image'] ?? null;
        $limitReached = false;
        $effectiveMax = 0;

        try {
            DB::transaction(function () use ($provider, $validated, $slug, $detailData, &$limitReached, &$effectiveMax) {
                $lockedProvider = Provider::where('id', $provider->id)
                    ->lockForUpdate()
                    ->first();

                $max = $lockedProvider->max_listings;

                if ($max === -1 || $max === null) {
                    $max = PHP_INT_MAX;
                }

                $effectiveMax = $max;

                $activeCount = Service::where('provider_id', $lockedProvider->id)
                    ->where('status', 'active')
                    ->count();

                if ($activeCount >= $max) {
                    $limitReached = true;
                    return;
                }

                $service = Service::create($validated);
                $this->upsertDetailRecord($service, $slug, $detailData);
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

    private function validateDetailFields(Request $request, string $slug): array
    {
        return match ($slug) {
            'trek' => $request->validate([
                'duration_days' => 'required|integer|min:1',
                'difficulty'    => 'required|in:easy,moderate,hard',
                'max_pax'       => 'nullable|integer|min:1',
                'max_altitude'  => 'nullable|integer|min:0',
                'season'        => 'nullable|string|max:100',
            ]),
            'tour' => $request->validate([
                'duration_days' => 'required|integer|min:1',
                'max_pax'       => 'nullable|integer|min:1',
            ]),
            'hotel' => $request->validate([
                'room_count'     => 'nullable|integer|min:0',
                'star_rating'    => 'nullable|integer|between:1,5',
                'amenities'      => 'nullable|string',
                'check_in_time'  => 'nullable|date_format:H:i',
                'check_out_time' => 'nullable|date_format:H:i',
            ]),
            'activity', 'experience' => $request->validate([
                'max_pax' => 'nullable|integer|min:1',
            ]),
            'transport' => $request->validate([
                'transport_type'    => 'required|in:bus,jeep,car,van,flight,heli',
                'from_location_id'  => 'required|exists:locations,id',
                'to_location_id'    => 'required|exists:locations,id',
                'departure_time'    => 'nullable|date_format:H:i,H:i:s',
                'duration_hours'    => 'required|numeric|min:0.25',
                'price_per_person'  => 'nullable|numeric|min:0',
                'price_per_vehicle' => 'nullable|numeric|min:0',
                'total_seats'       => 'required|integer|min:1',
                'private_shared'    => 'required|in:private,shared',
                'booking_type'      => 'nullable|in:instant,on-request',
                'ac_available'      => 'nullable|boolean',
                'driver_included'   => 'nullable|boolean',
                'fuel_included'     => 'nullable|boolean',
                'cancellation_policy' => 'nullable|string',
                'description'       => 'nullable|string',
            ]),
            default => [],
        };
    }

    private function upsertDetailRecord(Service $service, string $slug, array $data): void
    {
        if ($slug === 'hotel' && isset($data['amenities']) && is_string($data['amenities'])) {
            $data['amenities'] = array_values(array_filter(
                array_map('trim', explode(',', $data['amenities']))
            ));
        }

        if ($slug === 'transport') {
            $data['ac_available']    = !empty($data['ac_available']);
            $data['driver_included'] = !empty($data['driver_included']);
            $data['fuel_included']   = !empty($data['fuel_included']);
            if (isset($data['duration_hours'])) {
                $data['duration_minutes'] = (int) round($data['duration_hours'] * 60);
                unset($data['duration_hours']);
            }
        }

        match ($slug) {
            'trek'       => TrekDetail::updateOrCreate(['service_id' => $service->id], $data),
            'tour'       => TourDetail::updateOrCreate(['service_id' => $service->id], $data),
            'hotel'      => HotelDetail::updateOrCreate(['service_id' => $service->id], $data),
            'activity'   => ActivityDetail::updateOrCreate(['service_id' => $service->id], $data),
            'experience' => ExperienceDetail::updateOrCreate(['service_id' => $service->id], $data),
            'transport'  => TransportDetail::updateOrCreate(['service_id' => $service->id], $data),
            default      => null,
        };
    }

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

        $provider = Auth::user()->ownProvider();
        $providerType = $provider?->types()->first();
        $productCategorySlugs = ['shop', 'rental', 'wholesale'];

        if ($providerType && $providerType->serviceCategories->isNotEmpty()) {
            $allowedCategories = $providerType->serviceCategories->reject(function ($cat) use ($productCategorySlugs) {
                return in_array($cat->slug, $productCategorySlugs);
            });
        } else {
            $allowedCategories = ServiceCategory::whereNotIn('slug', $productCategorySlugs)->get();
        }

        $categories = $allowedCategories;

        $allowedIds = $allowedCategories->pluck('id')->all();
        $isLegacy = !in_array((int) $service->service_category_id, $allowedIds, true);
        $currentCategory = ServiceCategory::find($service->service_category_id);

        $locations = Location::whereNotNull('city')->where('city', '!=', '')->orderBy('city')->get(['id', 'city']);

        return view('provider.services.edit', compact(
            'service', 'categories', 'allowedCategories', 'providerType',
            'isLegacy', 'currentCategory', 'locations'
        ));
    }

    public function update(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'required|in:USD,NPR',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        $provider = Auth::user()->ownProvider();
        $providerType = $provider?->types()->first();
        $productCategorySlugs = ['shop', 'rental', 'wholesale'];

        if ($providerType && $providerType->serviceCategories->isNotEmpty()) {
            $allowedIds = $providerType->serviceCategories->reject(function($cat) use ($productCategorySlugs) {
                return in_array($cat->slug, $productCategorySlugs);
            })->pluck('id')->all();

            $submittedId = (int) $validated['service_category_id'];

            if (!in_array($submittedId, $allowedIds, true)) {
                $isLegacyUnchanged = (int) $service->service_category_id === $submittedId;
                if (!$isLegacyUnchanged) {
                    abort(403, __('messages.category_not_allowed'));
                }
            }
        }

        $category   = ServiceCategory::find($validated['service_category_id']);
        $slug       = $category ? $category->slug : '';
        $detailData = $this->validateDetailFields($request, $slug);

        if ($request->hasFile('cover_image')) {
            if ($service->cover_image) {
                \Storage::disk('public')->delete($service->cover_image);
            }
            $path = $request->file('cover_image')->store('services', 'public');
            $validated['cover_image'] = $path;
        }

        $service->update($validated);
        $this->upsertDetailRecord($service, $slug, $detailData);

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