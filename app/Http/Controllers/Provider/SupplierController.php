<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    protected function resolveProvider(): Provider
    {
        $user = Auth::user();
        if (!$user || !$user->isProviderOwner()) {
            abort(403, 'Only provider owners can manage suppliers.');
        }
        $provider = $user->ownProvider();
        if (!$provider) {
            abort(403, 'Only provider owners can manage suppliers.');
        }
        return $provider;
    }

    protected function getProviderLayout(Provider $provider): string
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
        return $map[$provider->types->first()?->slug] ?? 'layouts.provider';
    }

    public function index()
    {
        $provider = $this->resolveProvider();
        $suppliers = Supplier::where('provider_id', $provider->id)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(20);
        $provider_layout = $this->getProviderLayout($provider);
        return view('provider.suppliers.index', compact('suppliers', 'provider_layout'));
    }

    public function create()
    {
        $provider = $this->resolveProvider();
        $provider_layout = $this->getProviderLayout($provider);
        return view('provider.suppliers.create', compact('provider_layout'));
    }

    public function store(Request $request)
    {
        $provider = $this->resolveProvider();
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes'   => 'nullable|string|max:2000',
        ]);
        $validated['provider_id'] = $provider->id;
        $validated['is_active'] = true;
        Supplier::create($validated);
        return redirect()->route('provider.suppliers.index')
            ->with('success', 'Supplier added.');
    }

    public function edit(Supplier $supplier)
    {
        $provider = $this->resolveProvider();
        if ($supplier->provider_id !== $provider->id) abort(403);
        $provider_layout = $this->getProviderLayout($provider);
        return view('provider.suppliers.edit', compact('supplier', 'provider_layout'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $provider = $this->resolveProvider();
        if ($supplier->provider_id !== $provider->id) abort(403);
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:50',
            'email'     => 'nullable|email|max:255',
            'address'   => 'nullable|string|max:500',
            'notes'     => 'nullable|string|max:2000',
            'is_active' => 'sometimes|boolean',
        ]);
        $supplier->update($validated);
        return redirect()->route('provider.suppliers.index')
            ->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $provider = $this->resolveProvider();
        if ($supplier->provider_id !== $provider->id) abort(403);
        $supplier->delete();
        return redirect()->route('provider.suppliers.index')
            ->with('success', 'Supplier removed.');
    }
}