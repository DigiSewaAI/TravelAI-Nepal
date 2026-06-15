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
            'category'      => 'required|in:trek,tour,hotel',   // ✅ NEW
            'price'         => 'required|numeric|min:0',
            'itinerary_lines' => 'nullable|string',
            'cover_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery.*'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['agency_id'] = Auth::guard('agency')->id();

        // Convert itinerary lines to JSON
        if ($request->filled('itinerary_lines')) {
            $lines = explode("\n", trim($request->itinerary_lines));
            $lines = array_filter(array_map('trim', $lines));
            $validated['itinerary'] = json_encode($lines);
        } else {
            $validated['itinerary'] = null;
        }

        // Remove the temporary field so it doesn't cause SQL error
        unset($validated['itinerary_lines']);

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

        // Convert stored JSON itinerary to plain lines for the textarea
        $itineraryLines = '';
        if ($trek->itinerary) {
            $days = json_decode($trek->itinerary, true);
            if (is_array($days)) {
                $itineraryLines = implode("\n", $days);
            }
        }

        return view('agency.treks.edit', compact('trek', 'itineraryLines'));
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
            'category' => 'required|in:trek,tour,hotel',   // ✅ NEW
            'price' => 'required|numeric|min:0',
            'itinerary_lines' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['name', 'duration_days', 'difficulty', 'category', 'price']);  // ✅ added 'category'

        // Convert itinerary lines to JSON
        if ($request->filled('itinerary_lines')) {
            $lines = explode("\n", trim($request->itinerary_lines));
            $lines = array_filter(array_map('trim', $lines));
            $data['itinerary'] = json_encode($lines);
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