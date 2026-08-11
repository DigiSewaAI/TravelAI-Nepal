<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $provider = Auth::user()->ownProvider();
        $services = $provider ? $provider->services : collect();
        return view('provider.services.index', compact('services'));
    }

    public function create()
    {
        $categories = ServiceCategory::all();
        return view('provider.services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $provider = Auth::user()->ownProvider();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('services', 'public');
            $validated['cover_image'] = $path;
        }

        $validated['provider_id'] = $provider->id;
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(6);
        $validated['status'] = $validated['status'] ?? 'active';

        Service::create($validated);

        return redirect()->route('provider.services.index')->with('success', 'Service created successfully.');
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