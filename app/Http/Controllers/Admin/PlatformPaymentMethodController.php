<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlatformPaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PlatformPaymentMethod::orderBy('sort_order')->get();
        return view('admin.platform-payment-methods.index', compact('methods'));
    }

    public function create()
    {
        return view('admin.platform-payment-methods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'           => 'required|in:bank,esewa,khalti,other',
            'label'          => 'required|string|max:100',
            'account_name'   => 'nullable|string|max:150',
            'account_number' => 'nullable|string|max:50',
            'identifier'     => 'nullable|string|max:50',
            'bank_name'      => 'nullable|string|max:100',
            'currency'       => 'required|string|max:3',
            'instructions'   => 'nullable|string|max:1000',
            'is_active'      => 'boolean',
            'sort_order'     => 'nullable|integer',
            'qr_image'       => 'nullable|image|max:2048',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('qr_image')) {
            $validated['qr_image_path'] = $request->file('qr_image')->store('platform-qr', 'public');
        }

        PlatformPaymentMethod::create($validated);

        return redirect()->route('admin.platform-payment-methods.index')
            ->with('success', 'Payment method added successfully.');
    }

    public function edit(PlatformPaymentMethod $platformPaymentMethod)
    {
        return view('admin.platform-payment-methods.edit', ['method' => $platformPaymentMethod]);
    }

    public function update(Request $request, PlatformPaymentMethod $platformPaymentMethod)
    {
        $validated = $request->validate([
            'type'           => 'required|in:bank,esewa,khalti,other',
            'label'          => 'required|string|max:100',
            'account_name'   => 'nullable|string|max:150',
            'account_number' => 'nullable|string|max:50',
            'identifier'     => 'nullable|string|max:50',
            'bank_name'      => 'nullable|string|max:100',
            'currency'       => 'required|string|max:3',
            'instructions'   => 'nullable|string|max:1000',
            'is_active'      => 'boolean',
            'sort_order'     => 'nullable|integer',
            'qr_image'       => 'nullable|image|max:2048',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('qr_image')) {
            if ($platformPaymentMethod->qr_image_path) {
                Storage::disk('public')->delete($platformPaymentMethod->qr_image_path);
            }
            $validated['qr_image_path'] = $request->file('qr_image')->store('platform-qr', 'public');
        }

        $platformPaymentMethod->update($validated);

        return redirect()->route('admin.platform-payment-methods.index')
            ->with('success', 'Payment method updated successfully.');
    }

    public function destroy(PlatformPaymentMethod $platformPaymentMethod)
    {
        if ($platformPaymentMethod->qr_image_path) {
            Storage::disk('public')->delete($platformPaymentMethod->qr_image_path);
        }
        $platformPaymentMethod->delete();
        return back()->with('success', 'Payment method deleted.');
    }

    public function toggle(PlatformPaymentMethod $platformPaymentMethod)
    {
        $platformPaymentMethod->update(['is_active' => !$platformPaymentMethod->is_active]);
        return back()->with('success', 'Status updated.');
    }
}