<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
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
        ]);
    }

    public function index()
    {
        $provider = $this->provider();
        $methods  = $provider->paymentMethods()->orderBy('sort_order')->orderBy('id')->get();

        return view('provider.settings.payment-methods', [
            'provider'      => $provider,
            'domestic'      => $methods->whereIn('type', self::DOMESTIC_TYPES),
            'international' => $methods->whereIn('type', self::INTERNATIONAL_TYPES),
        ]);
    }

    public function store(Request $request)
    {
        $provider  = $this->provider();
        $validated = $this->validated($request);

        if ($request->hasFile('qr_image')) {
            $validated['qr_image_path'] = $request->file('qr_image')
                ->store('provider-qr/' . $provider->id, 'public');
        }
        unset($validated['qr_image']);

        $validated['provider_id'] = $provider->id;
        $validated['currency']    = $validated['currency'] ?? 'NPR';
        $validated['is_active']   = true;
        $validated['sort_order']  = $validated['sort_order'] ?? 0;

        ProviderPaymentMethod::create($validated);

        return back()->with('success', __('messages.pm_created'));
    }

    public function update(Request $request, $id)
    {
        $provider = $this->provider();
        $method   = ProviderPaymentMethod::where('provider_id', $provider->id)->findOrFail($id);

        $validated = $this->validated($request);

        if ($request->hasFile('qr_image')) {
            if ($method->qr_image_path) {
                Storage::disk('public')->delete($method->qr_image_path);
            }
            $validated['qr_image_path'] = $request->file('qr_image')
                ->store('provider-qr/' . $provider->id, 'public');
        }
        unset($validated['qr_image']);

        $method->update($validated);

        return back()->with('success', __('messages.pm_updated'));
    }

    public function destroy($id)
    {
        $provider = $this->provider();
        $method   = ProviderPaymentMethod::where('provider_id', $provider->id)->findOrFail($id);

        if ($method->qr_image_path) {
            Storage::disk('public')->delete($method->qr_image_path);
        }

        $method->delete();

        return back()->with('success', __('messages.pm_deleted'));
    }

    public function toggle($id)
    {
        $provider = $this->provider();
        $method   = ProviderPaymentMethod::where('provider_id', $provider->id)->findOrFail($id);

        $method->update(['is_active' => !$method->is_active]);

        return back()->with('success', __('messages.pm_toggled'));
    }
}