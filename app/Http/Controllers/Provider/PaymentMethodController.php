<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    /**
     * Resolve vertical-specific layout based on provider type.
     */
    private function getProviderLayout($provider): string
    {
        $map = [
            'restaurant'         => 'layouts.provider-restaurant',
            'hotel'              => 'layouts.provider-hotel',
            'resort'             => 'layouts.provider-hotel',
            'lodge'              => 'layouts.provider-hotel',
            'homestay'           => 'layouts.provider-hotel',
            'transport-provider' => 'layouts.provider-transport',
            'shop-owner'         => 'layouts.provider-inventory',
            'rental-provider'    => 'layouts.provider-inventory',
            'wholesale-provider' => 'layouts.provider-inventory',
            'art-craft'          => 'layouts.provider-art-craft',
            'guide'              => 'layouts.provider-individual',
            'porter'             => 'layouts.provider-individual',
            'photographer'       => 'layouts.provider-individual',
            'money-changer'      => 'layouts.provider-money-changer',
            'rescue-service'     => 'layouts.provider-rescue',
        ];
        return $map[$provider?->types->first()?->slug] ?? 'layouts.provider';
    }
    private const DOMESTIC_TYPES = ['bank', 'esewa', 'khalti', 'cash'];
    private const INTERNATIONAL_TYPES = ['paypal', 'wise', 'international_bank'];

    private function provider()
    {
        $provider = Auth::user()->ownProvider();
        if (!$provider) {
            abort(403, 'No provider found.');
        }
        return $provider;
    }
    private function isTransportProvider($provider): bool
    {
        return $provider->types->contains('slug', 'transport-provider');
    }
    private function validated(Request $request): array
    {
        return $request->validate([
            'type'           => 'required|in:' . implode(',', array_merge(self::DOMESTIC_TYPES, self::INTERNATIONAL_TYPES)),
            'label'          => 'nullable|string|max:100',
            'account_name'   => 'nullable|string|max:150',
            'account_number' => 'nullable|string|max:100',
            'identifier'     => 'nullable|string|max:150',
            'bank_name'      => 'nullable|string|max:150',
            'swift_code'     => 'nullable|string|max:30',
            'qr_image'       => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'currency'       => 'nullable|string|size:3',
            'instructions'   => 'nullable|string|max:1000',
            'sort_order'     => 'nullable|integer|min:0|max:999',
            'is_primary'     => 'nullable|boolean',

        ]);
    }

    public function index()
    {
        $provider = $this->provider();
        $methods  = $provider->paymentMethods()->orderBy('sort_order')->orderBy('id')->get();

        $provider_layout = $this->getProviderLayout($provider);
return view(($provider_layout === 'layouts.provider') ? 'provider.settings.payment-methods' : 'provider.settings.payment-methods-premium', [
            'provider'      => $provider,
            'domestic'      => $methods->whereIn('type', self::DOMESTIC_TYPES),
            'international' => $methods->whereIn('type', self::INTERNATIONAL_TYPES),
            'provider_layout' => $provider_layout
        ]);
    }

    public function store(Request $request)
    {
        $provider  = $this->provider();
        $validated = $this->validated($request);

        $qrPath = null;
        if ($request->hasFile('qr_image')) {
            $qrPath = $request->file('qr_image')->store('provider-qr/' . $provider->id, 'public');
        }
        unset($validated['qr_image']);
        if ($qrPath) $validated['qr_image_path'] = $qrPath;

        $validated['provider_id'] = $provider->id;
        $validated['currency']    = $validated['currency'] ?? 'NPR';
        $validated['is_active']   = true;
        $validated['sort_order']  = $validated['sort_order'] ?? 0;

        $wantsPrimary = $request->boolean('is_primary');
        unset($validated['is_primary']);

        try {
            \DB::transaction(function () use ($provider, $validated, $wantsPrimary) {
                \App\Models\Provider::where('id', $provider->id)->lockForUpdate()->first();

                $hasPrimary = ProviderPaymentMethod::where('provider_id', $provider->id)
                    ->where('is_primary', true)->exists();

                $makePrimary = $wantsPrimary || !$hasPrimary;

                if ($makePrimary && $hasPrimary) {
                    ProviderPaymentMethod::where('provider_id', $provider->id)
                        ->where('is_primary', true)->update(['is_primary' => false]);
                }

                $validated['is_primary'] = $makePrimary;
                ProviderPaymentMethod::create($validated);
            });
        } catch (\Throwable $e) {
            // Orphan cleanup: DB failed → delete uploaded file
            if ($qrPath) \Storage::disk('public')->delete($qrPath);
            throw $e;
        }

        return back()->with('success', __('messages.pm_created'));
    }

    public function update(Request $request, $id)
    {
        $provider = $this->provider();
        $method   = ProviderPaymentMethod::where('provider_id', $provider->id)->findOrFail($id);

        $validated = $this->validated($request);

        $newQrPath = null;
        $oldQrPath = $method->qr_image_path;
        if ($request->hasFile('qr_image')) {
            $newQrPath = $request->file('qr_image')->store('provider-qr/' . $provider->id, 'public');
            $validated['qr_image_path'] = $newQrPath;
        }
        unset($validated['qr_image']);

        $wantsPrimary = $request->boolean('is_primary');
        unset($validated['is_primary']);

        $willBeActive = array_key_exists('is_active', $validated) ? (bool)$validated['is_active'] : $method->is_active;

        if ($wantsPrimary && !$willBeActive) {
            if ($newQrPath) \Storage::disk('public')->delete($newQrPath);
            return back()->withErrors(['is_primary' => __('messages.pm_cannot_set_inactive_primary')])->withInput();
        }

        try {
            \DB::transaction(function () use ($provider, $method, $validated, $wantsPrimary) {
                \App\Models\Provider::where('id', $provider->id)->lockForUpdate()->first();

                if ($wantsPrimary && !$method->is_primary) {
                    ProviderPaymentMethod::where('provider_id', $provider->id)
                        ->where('id', '!=', $method->id)
                        ->where('is_primary', true)
                        ->update(['is_primary' => false]);
                    $validated['is_primary'] = true;
                }

                $method->update($validated);
            });
        } catch (\Throwable $e) {
            if ($newQrPath) \Storage::disk('public')->delete($newQrPath);
            throw $e;
        }

        // Old QR delete AFTER DB commit
        if ($newQrPath && $oldQrPath) {
            \Storage::disk('public')->delete($oldQrPath);
        }

        return back()->with('success', __('messages.pm_updated'));
    }

    public function destroy($id)
    {
        $provider = $this->provider();
        $method   = ProviderPaymentMethod::where('provider_id', $provider->id)->findOrFail($id);

        $qrPath     = $method->qr_image_path;
        $wasPrimary = $method->is_primary;

        \DB::transaction(function () use ($provider, $method, $wasPrimary) {
            \App\Models\Provider::where('id', $provider->id)->lockForUpdate()->first();

            $method->delete();

            if ($wasPrimary) {
                $nextPrimary = ProviderPaymentMethod::where('provider_id', $provider->id)
                    ->where('is_active', true)
                    ->orderBy('sort_order')->orderBy('id')
                    ->first();
                if ($nextPrimary) {
                    $nextPrimary->update(['is_primary' => true]);
                }
                // कोई active नै भने — zero primary (कोई null नै)
            }
        });

        // QR delete AFTER DB commit
        if ($qrPath) \Storage::disk('public')->delete($qrPath);

        return back()->with('success', __('messages.pm_deleted'));
    }

        public function toggle($id)
    {
        $provider = $this->provider();
        $method   = ProviderPaymentMethod::where('provider_id', $provider->id)->findOrFail($id);

        if ($method->is_primary && $method->is_active) {
            return back()->withErrors(['primary' => __('messages.pm_cannot_disable_primary')]);
        }

        \DB::transaction(function () use ($provider, $method) {
            \App\Models\Provider::where('id', $provider->id)->lockForUpdate()->first();
            $method->update(['is_active' => !$method->is_active]);
        });

        return back()->with('success', __('messages.pm_toggled'));
    }
        public function setPrimary($id)
    {
        $provider = $this->provider();
        $method   = ProviderPaymentMethod::where('provider_id', $provider->id)->findOrFail($id);

        if (!$method->is_active) {
            return back()->withErrors(['primary' => __('messages.pm_cannot_set_inactive_primary')]);
        }

        \DB::transaction(function () use ($provider, $method) {
            \App\Models\Provider::where('id', $provider->id)->lockForUpdate()->first();

            ProviderPaymentMethod::where('provider_id', $provider->id)
                ->where('is_primary', true)->update(['is_primary' => false]);

            $method->update(['is_primary' => true]);
        });

        return back()->with('success', __('messages.pm_primary_set'));
    }
        public function updatePaymentModel(Request $request)
    {
        $provider = $this->provider();

        if (!$this->isTransportProvider($provider)) {
            abort(403, 'Payment model configuration is only available for transport providers.');
        }

        $validated = $request->validate([
            'transport_payment_model' => 'required|in:advance,pay_on_arrival,both',
        ]);

        $newModel = $validated['transport_payment_model'];

        if (in_array($newModel, ['advance', 'both'])) {
            $hasActive = ProviderPaymentMethod::where('provider_id', $provider->id)
                ->where('is_active', true)->exists();
            if (!$hasActive) {
                return back()->withErrors([
                    'transport_payment_model' => __('messages.pm_advance_requires_method'),
                ])->withInput();
            }
        }

        $provider->update(['transport_payment_model' => $newModel]);

        return back()->with('success', __('messages.pm_payment_model_updated'));
    }
}