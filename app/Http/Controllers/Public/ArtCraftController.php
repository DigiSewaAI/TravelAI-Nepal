<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Artwork;
use App\Models\CustomOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArtCraftController extends Controller
{
    public function show($slug)
    {
        $service = Service::with(['provider', 'category', 'artCraftDetail'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $provider = $service->provider;

        $artworks = Artwork::where('provider_id', $provider->id)
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        $categories = $artworks->pluck('category')->filter()->unique()->values();

        $offers = \App\Models\ProviderOffer::where('provider_id', $provider->id)
            ->where('is_active', true)
            ->where('valid_until', '>=', now())
            ->orderBy('valid_until')
            ->get();

        return view('public.artcraft.show', compact(
            'service', 'provider', 'artworks', 'categories', 'offers'
        ));
    }

    public function artworkDetail($slug, $artworkId)
    {
        $service = Service::with(['provider', 'category'])->where('slug', $slug)->where('status', 'active')->firstOrFail();
        $provider = $service->provider;

        $artwork = Artwork::where('id', $artworkId)
            ->where('provider_id', $provider->id)
            ->where('is_published', true)
            ->firstOrFail();

        return view('public.artcraft.artwork-detail', compact('service', 'provider', 'artwork'));
    }

    public function enquireReservation(Request $request, $slug, $artworkId)
    {
        $service = Service::with('provider')->where('slug', $slug)->firstOrFail();
        $provider = $service->provider;

        $artwork = Artwork::where('id', $artworkId)
            ->where('provider_id', $provider->id)
            ->where('is_published', true)
            ->firstOrFail();

        if ($artwork->status !== 'available') {
            return back()->withErrors(['artwork' => 'This artwork is no longer available.'])->withInput();
        }

        $validated = $request->validate([
            'client_name'  => 'required|string|max:150',
            'client_email' => 'required|email|max:150',
            'message'      => 'nullable|string|max:1000',
        ]);

        $order = DB::transaction(function () use ($provider, $artwork, $validated) {
            return CustomOrder::create([
                'provider_id' => $provider->id,
                'client_name' => $validated['client_name'],
                'client_email' => $validated['client_email'],
                'type' => $artwork->category ?? 'Handmade',
                'budget' => $artwork->price,
                'deadline' => null,
                'status' => 'pending',
                'request_type' => 'ready_made',
                'ready_made_artwork_id' => $artwork->id,
                'artwork_name_snapshot' => $artwork->name,
                'artwork_price_snapshot' => $artwork->price,
                'artwork_currency_snapshot' => 'NPR',
                'description' => $validated['message'] ?? null,
                'quantity' => 1,
            ]);
        });

        try {
            \Mail::to($order->client_email)->queue(new \App\Mail\ArtCraftEnquiryReceivedMail($order));
        } catch (\Throwable $e) {
            \Log::warning('ArtCraft enquiry mail failed', ['order_id' => $order->id, 'e' => $e->getMessage()]);
        }

        try {
            $providerUser = $provider->user;
            if ($providerUser) {
                $providerUser->notify(new \App\Notifications\NewArtCraftEnquiryNotification($order));
            }
        } catch (\Throwable $e) {
            \Log::warning('ArtCraft enquiry notify failed', ['order_id' => $order->id]);
        }

        return redirect()->route('public.artcraft.show', $slug)
            ->with('success', 'Your enquiry has been sent! The artist will contact you shortly.');
    }

    public function customCommissionForm($slug)
    {
        $service = Service::with(['provider', 'category'])->where('slug', $slug)->where('status', 'active')->firstOrFail();
        $provider = $service->provider;

        return view('public.artcraft.custom-commission', compact('service', 'provider'));
    }

    public function submitCustomCommission(Request $request, $slug)
    {
        $service = Service::with('provider')->where('slug', $slug)->firstOrFail();
        $provider = $service->provider;

        $validated = $request->validate([
            'client_name'  => 'required|string|max:150',
            'client_email' => 'required|email|max:150',
            'type'         => 'required|string|max:100',
            'description'  => 'required|string|max:2000',
            'materials'    => 'nullable|string|max:255',
            'dimensions'   => 'nullable|string|max:100',
            'quantity'     => 'required|integer|min:1|max:100',
            'budget'       => 'required|numeric|min:0',
            'deadline'     => 'nullable|date|after_or_equal:today',
            'pickup_address' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string|max:255',
            'reference_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $referencePath = null;
        if ($request->hasFile('reference_image')) {
            $referencePath = $request->file('reference_image')->store(
                'order-' . time() . '-' . Str::random(8),
                'artcraft-references'
            );
        }

        $order = DB::transaction(function () use ($provider, $validated, $referencePath) {
            return CustomOrder::create([
                'provider_id' => $provider->id,
                'client_name' => $validated['client_name'],
                'client_email' => $validated['client_email'],
                'type' => $validated['type'],
                'budget' => $validated['budget'],
                'deadline' => $validated['deadline'] ?? null,
                'status' => 'pending',
                'request_type' => 'custom',
                'description' => $validated['description'],
                'materials' => $validated['materials'] ?? null,
                'dimensions' => $validated['dimensions'] ?? null,
                'quantity' => $validated['quantity'],
                'reference_image_path' => $referencePath,
                'pickup_address' => $validated['pickup_address'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
            ]);
        });

        try {
            \Mail::to($order->client_email)->queue(new \App\Mail\ArtCraftEnquiryReceivedMail($order));
        } catch (\Throwable $e) {
            \Log::warning('ArtCraft custom mail failed', ['order_id' => $order->id]);
        }

        try {
            $providerUser = $provider->user;
            if ($providerUser) {
                $providerUser->notify(new \App\Notifications\NewArtCraftEnquiryNotification($order));
            }
        } catch (\Throwable $e) {
            \Log::warning('ArtCraft custom notify failed', ['order_id' => $order->id]);
        }

        return redirect()->route('public.artcraft.show', $slug)
            ->with('success', 'Your custom commission request has been sent! The artist will contact you with a quote.');
    }
}