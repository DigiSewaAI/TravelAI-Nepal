<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Trek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TrekController extends Controller
{
    // No __construct() – middleware is applied via routes

    public function index()
    {
        $treks = Auth::guard('agency')->user()->treks()->latest()->get();
        return view('agency.treks.index', compact('treks'));
    }

    public function create()
    {
        return view('agency.treks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'difficulty'    => 'required|in:easy,moderate,hard',
            'price'         => 'required|numeric|min:0',
            'itinerary'     => 'nullable|string',
            'cover_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery.*'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['agency_id'] = Auth::guard('agency')->id();

        // Handle itinerary JSON (same logic as update)
        if ($request->filled('itinerary')) {
            $itinerary = json_decode($request->itinerary, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($itinerary)) {
                return back()->withErrors(['itinerary' => 'Please enter a valid JSON array.'])->withInput();
            }
            $validated['itinerary'] = json_encode($itinerary);
        } else {
            $validated['itinerary'] = null;
        }

        // Handle cover image
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('trek-covers', 'public');
            $validated['cover_image'] = $path;
        }

        // Handle gallery images
        if ($request->hasFile('gallery')) {
            $galleryPaths = [];
            foreach ($request->file('gallery') as $img) {
                $galleryPaths[] = $img->store('trek-galleries', 'public');
            }
            $validated['gallery'] = json_encode($galleryPaths);
        }

        Trek::create($validated);

        return redirect()->route('agency.treks.index')->with('success', 'Trek created successfully.');
    }

    public function edit(Trek $trek)
{
    if ($trek->agency_id !== Auth::guard('agency')->id()) {
        abort(403);
    }
    $itinerary = $trek->itinerary 
        ? json_encode(json_decode($trek->itinerary, true), JSON_PRETTY_PRINT) 
        : '';
    return view('agency.treks.edit', compact('trek', 'itinerary'));
}

    public function update(Request $request, Trek $trek)
{
    if ($trek->agency_id !== Auth::guard('agency')->id()) {
        abort(403);
    }

    $request->validate([
        'name' => 'required|string|max:255',
        'duration_days' => 'required|integer|min:1',
        'difficulty' => 'required|in:easy,moderate,hard',
        'price' => 'required|numeric|min:0',
        'itinerary' => 'nullable|string',
        'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $data = $request->only(['name', 'duration_days', 'difficulty', 'price']);

    // Correct itinerary handling
    if ($request->filled('itinerary')) {
        $itinerary = json_decode($request->itinerary, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($itinerary)) {
            return back()->withErrors(['itinerary' => 'Please enter a valid JSON array.'])->withInput();
        }
        $data['itinerary'] = json_encode($itinerary);
    } else {
        $data['itinerary'] = null;
    }

    // Handle cover image
    if ($request->hasFile('cover_image')) {
        if ($trek->cover_image) {
            Storage::disk('public')->delete($trek->cover_image);
        }
        $data['cover_image'] = $request->file('cover_image')->store('trek-covers', 'public');
    }

    // Handle gallery
    if ($request->hasFile('gallery')) {
        if ($trek->gallery) {
            $old = json_decode($trek->gallery, true);
            foreach ($old as $img) {
                Storage::disk('public')->delete($img);
            }
        }
        $galleryPaths = [];
        foreach ($request->file('gallery') as $img) {
            $galleryPaths[] = $img->store('trek-galleries', 'public');
        }
        $data['gallery'] = json_encode($galleryPaths);
    }

    $trek->update($data);
    return redirect()->route('agency.treks.index')->with('success', 'Trek updated successfully.');
}

    public function destroy(Trek $trek)
    {
        if ($trek->agency_id !== Auth::guard('agency')->id()) {
            abort(403);
        }

        // Delete associated images
        if ($trek->cover_image) {
            Storage::disk('public')->delete($trek->cover_image);
        }
        if ($trek->gallery) {
            $gallery = json_decode($trek->gallery, true);
            foreach ($gallery as $img) {
                Storage::disk('public')->delete($img);
            }
        }

        $trek->delete();
        return redirect()->route('agency.treks.index')->with('success', 'Trek deleted successfully.');
    }
}