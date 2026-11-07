<?php
/**
 * ADD — Hotel & ArtCraft Claim Detail Pages
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  ADD — HOTEL & ARTCRAFT DETAIL PAGES\n";
echo "═══════════════════════════════════════════════════════\n\n";

// ═══════════════════════════════════════════════════════
// [HOTEL] 1. Controller show() method
// ═══════════════════════════════════════════════════════
echo "─── [HOTEL] Controller ───\n";
$hotelCtrl = 'app/Http/Controllers/Traveler/HotelClaimController.php';
copy($hotelCtrl, $hotelCtrl . '.bak_detail');
$hc = file_get_contents($hotelCtrl);

if (!str_contains($hc, 'public function show(')) {
    $hotelShow = <<<'PHP'

    public function show($booking)
    {
        $user = Auth::user();
        $claim = \App\Models\BookingClaim::where('claimable_type', \App\Models\HotelBooking::class)
            ->where('claimable_id', $booking)
            ->where('traveler_id', $user->id)
            ->first();
        if (!$claim) abort(404);
        $booking = \App\Models\HotelBooking::with(['provider'])->findOrFail($booking);
        return view('traveler.hotel.claim-show', ['booking' => $booking, 'claim' => $claim]);
    }
}
PHP;
    $lastBrace = strrpos($hc, '}');
    $hc = substr($hc, 0, $lastBrace) . $hotelShow . substr($hc, $lastBrace + 1);
    file_put_contents($hotelCtrl, $hc);
    echo "  ✅ Hotel show() added\n";
}

// ═══════════════════════════════════════════════════════
// [ARTCRAFT] 1. Controller show() method
// ═══════════════════════════════════════════════════════
echo "─── [ARTCRAFT] Controller ───\n";
$artCtrl = 'app/Http/Controllers/Traveler/ArtCraftClaimController.php';
if (file_exists($artCtrl)) {
    copy($artCtrl, $artCtrl . '.bak_detail');
    $ac = file_get_contents($artCtrl);
    if (!str_contains($ac, 'public function show(')) {
        $artShow = <<<'PHP'

    public function show($order)
    {
        $user = Auth::user();
        $claim = \App\Models\BookingClaim::where('claimable_type', \App\Models\CustomOrder::class)
            ->where('claimable_id', $order)
            ->where('traveler_id', $user->id)
            ->first();
        if (!$claim) abort(404);
        $order = \App\Models\CustomOrder::findOrFail($order);
        return view('traveler.artcraft.claim-show', ['order' => $order, 'claim' => $claim]);
    }
}
PHP;
        $lastBrace = strrpos($ac, '}');
        $ac = substr($ac, 0, $lastBrace) . $artShow . substr($ac, $lastBrace + 1);
        file_put_contents($artCtrl, $ac);
        echo "  ✅ ArtCraft show() added\n";
    }
} else {
    echo "  ⚠️ ArtCraftClaimController not found\n";
}

// ═══════════════════════════════════════════════════════
// ROUTES — Add both
// ═══════════════════════════════════════════════════════
echo "\n─── Routes ───\n";
$routesFile = 'routes/web.php';
copy($routesFile, $routesFile . '.bak_hotel_art');
$r = file_get_contents($routesFile);

// Hotel route
if (!str_contains($r, 'traveler.hotel.claim.show')) {
    $hotelVerifyLine = "Route::post('/claim/verify/{token}', [\\App\\Http\\Controllers\\Traveler\\HotelClaimController::class, 'verify'])";
    if (str_contains($r, $hotelVerifyLine)) {
        $pos = strpos($r, $hotelVerifyLine);
        $endPos = strpos($r, ';', $pos);
        $newRoute = "\n        Route::get('/claim/show/{booking}', [\\App\\Http\\Controllers\\Traveler\\HotelClaimController::class, 'show'])->name('claim.show');";
        $r = substr($r, 0, $endPos + 1) . $newRoute . substr($r, $endPos + 1);
        echo "  ✅ Hotel route added\n";
    }
}

// ArtCraft route
if (!str_contains($r, 'traveler.artcraft.claim.show')) {
    $artVerifyLine = "Route::post('/claim/verify/{token}', [\\App\\Http\\Controllers\\Traveler\\ArtCraftClaimController::class, 'verify'])";
    if (str_contains($r, $artVerifyLine)) {
        $pos = strpos($r, $artVerifyLine);
        $endPos = strpos($r, ';', $pos);
        $newRoute = "\n        Route::get('/claim/show/{order}', [\\App\\Http\\Controllers\\Traveler\\ArtCraftClaimController::class, 'show'])->name('claim.show');";
        $r = substr($r, 0, $endPos + 1) . $newRoute . substr($r, $endPos + 1);
        echo "  ✅ ArtCraft route added\n";
    }
}

file_put_contents($routesFile, $r);

// ═══════════════════════════════════════════════════════
// VIEWS — Hotel & ArtCraft
// ═══════════════════════════════════════════════════════
echo "\n─── Views ───\n";

$hotelView = <<<'BLADE'
@extends('layouts.traveler-v2')
@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <a href="{{ route('traveler.dashboard-v2') }}" style="display:inline-flex; align-items:center; gap:8px; color:#2563eb; font-weight:600; text-decoration:none; margin-bottom:20px; font-size:14px;">← Back to Dashboard</a>
    <div style="background:linear-gradient(135deg,#ede9fe,#ddd6fe); border-radius:18px; padding:24px; margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
            <div>
                <div style="font-size:13px; font-weight:700; color:#5b21b6; text-transform:uppercase;">🏨 Hotel Booking</div>
                <h1 style="font-size:24px; font-weight:800; color:#111827; margin:6px 0;">{{ $booking->guest_name ?? 'Guest' }}</h1>
                @if($booking->check_in_date)
                <p style="color:#5b21b6; font-size:14px; margin:0;">📅 {{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }}{{ $booking->check_out_date ? ' → ' . \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') : '' }}</p>
                @endif
            </div>
            <span style="background:rgba(255,255,255,0.7); color:#5b21b6; padding:6px 16px; border-radius:999px; font-size:13px; font-weight:700;">{{ ucfirst($booking->status) }}</span>
        </div>
    </div>
    <div style="background:#fff; border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid #e5e7eb;">
        <h2 style="font-size:16px; font-weight:800; color:#111827; margin:0 0 14px;">🏨 Booking Details</h2>
        @if($booking->provider)<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Provider</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $booking->provider->name ?? 'N/A' }}</span></div>@endif
        @if($booking->room)<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Room</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $booking->room->name ?? $booking->room_id }}</span></div>@endif
        @if($booking->nights)<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Nights</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $booking->nights }}</span></div>@endif
        @if($booking->guest_email)<div style="display:flex; justify-content:space-between; padding:8px 0;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Email</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $booking->guest_email }}</span></div>@endif
    </div>
    <div style="background:#fff; border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid #e5e7eb;">
        <h2 style="font-size:16px; font-weight:800; color:#111827; margin:0 0 14px;">💰 Payment</h2>
        <div style="display:flex; justify-content:space-between; padding:10px 0; border-top:2px solid #e5e7eb;"><span style="font-size:16px; font-weight:800;">Total</span><span style="font-size:18px; font-weight:800; color:#059669;">NPR {{ number_format($booking->amount ?? 0, 2) }}</span></div>
    </div>
    <div style="background:#ecfdf5; border-radius:16px; padding:16px; margin-bottom:20px; border:1px solid #a7f3d0;">
        <p style="font-size:13px; color:#065f46; margin:0; font-weight:600;">✅ Claimed on: {{ $claim->claimed_at ? $claim->claimed_at->translatedFormat('M d, Y') : 'N/A' }}</p>
    </div>
    <a href="{{ route('traveler.dashboard-v2') }}" style="display:block; text-align:center; background:#2563eb; color:#fff; padding:12px 20px; border-radius:12px; font-weight:700; text-decoration:none;">Back to Dashboard</a>
</div>
@endsection
BLADE;

$artView = <<<'BLADE'
@extends('layouts.traveler-v2')
@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <a href="{{ route('traveler.dashboard-v2') }}" style="display:inline-flex; align-items:center; gap:8px; color:#2563eb; font-weight:600; text-decoration:none; margin-bottom:20px; font-size:14px;">← Back to Dashboard</a>
    <div style="background:linear-gradient(135deg,#fce7f3,#fbcfe8); border-radius:18px; padding:24px; margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
            <div>
                <div style="font-size:13px; font-weight:700; color:#9f1239; text-transform:uppercase;">🎨 ArtCraft Order</div>
                <h1 style="font-size:24px; font-weight:800; color:#111827; margin:6px 0;">{{ ucfirst($order->type ?? 'ArtCraft') }} · {{ $order->client_name ?? 'Client' }}</h1>
                @if($order->deadline)<p style="color:#9f1239; font-size:14px; margin:0;">📅 Due: {{ \Carbon\Carbon::parse($order->deadline)->format('M d, Y') }}</p>@endif
            </div>
            <span style="background:rgba(255,255,255,0.7); color:#9f1239; padding:6px 16px; border-radius:999px; font-size:13px; font-weight:700;">{{ ucfirst($order->status) }}</span>
        </div>
    </div>
    <div style="background:#fff; border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid #e5e7eb;">
        <h2 style="font-size:16px; font-weight:800; color:#111827; margin:0 0 14px;">🎨 Order Details</h2>
        @if($order->client_name)<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Client</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $order->client_name }}</span></div>@endif
        @if($order->client_email)<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Email</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $order->client_email }}</span></div>@endif
        @if($order->type)<div style="display:flex; justify-content:space-between; padding:8px 0;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Type</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ ucfirst($order->type) }}</span></div>@endif
    </div>
    @if($order->description)
    <div style="background:#fff; border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid #e5e7eb;">
        <h2 style="font-size:16px; font-weight:800; color:#111827; margin:0 0 10px;">📝 Description</h2>
        <p style="font-size:14px; color:#374151; line-height:1.6; margin:0;">{{ $order->description }}</p>
    </div>
    @endif
    <div style="background:#fff; border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid #e5e7eb;">
        <h2 style="font-size:16px; font-weight:800; color:#111827; margin:0 0 14px;">💰 Budget</h2>
        <div style="display:flex; justify-content:space-between; padding:10px 0; border-top:2px solid #e5e7eb;"><span style="font-size:16px; font-weight:800;">Total</span><span style="font-size:18px; font-weight:800; color:#059669;">NPR {{ number_format($order->budget ?? 0, 2) }}</span></div>
    </div>
    <div style="background:#ecfdf5; border-radius:16px; padding:16px; margin-bottom:20px; border:1px solid #a7f3d0;">
        <p style="font-size:13px; color:#065f46; margin:0; font-weight:600;">✅ Claimed on: {{ $claim->claimed_at ? $claim->claimed_at->translatedFormat('M d, Y') : 'N/A' }}</p>
    </div>
    <a href="{{ route('traveler.dashboard-v2') }}" style="display:block; text-align:center; background:#2563eb; color:#fff; padding:12px 20px; border-radius:12px; font-weight:700; text-decoration:none;">Back to Dashboard</a>
</div>
@endsection
BLADE;

if (!is_dir('resources/views/traveler/hotel')) mkdir('resources/views/traveler/hotel', 0755, true);
if (!is_dir('resources/views/traveler/artcraft')) mkdir('resources/views/traveler/artcraft', 0755, true);

file_put_contents('resources/views/traveler/hotel/claim-show.blade.php', $hotelView);
echo "  ✅ Hotel view created\n";
file_put_contents('resources/views/traveler/artcraft/claim-show.blade.php', $artView);
echo "  ✅ ArtCraft view created\n";

// ═══════════════════════════════════════════════════════
// BUTTONS in my-trips.blade.php
// ═══════════════════════════════════════════════════════
echo "\n─── Buttons in my-trips ───\n";
$myTrips = 'resources/views/traveler/_partials/my-trips.blade.php';
copy($myTrips, $myTrips . '.bak_hotel_art_btn');
$mt = file_get_contents($myTrips);

// Hotel button
if (!str_contains($mt, 'hotel.claim.show')) {
    $hotelOld = <<<'HTML'
                            <span style="background: rgba(255,255,255,0.25); color: #fff; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; margin-top: 10px; align-self: flex-start; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.35);">
                                <span class="tv2-check">✓</span> {{ ucfirst($hotelBooking->status) }}
                            </span>
HTML;
    $hotelNew = <<<'HTML'
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; margin-top: 10px; padding-right: 4px;">
                                <span style="background: rgba(255,255,255,0.25); color: #fff; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.35);">
                                    <span class="tv2-check">✓</span> {{ ucfirst($hotelBooking->status) }}
                                </span>
                                <a href="{{ route('traveler.hotel.claim.show', $hotelBooking->id) }}" style="background: rgba(255,255,255,0.95); color: #1e293b; padding: 5px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; text-decoration: none;">View</a>
                            </div>
HTML;
    if (str_contains($mt, trim($hotelOld))) {
        $mt = str_replace(trim($hotelOld), trim($hotelNew), $mt);
        echo "  ✅ Hotel View button added\n";
    }
}

// ArtCraft button
if (!str_contains($mt, 'artcraft.claim.show')) {
    $artOld = <<<'HTML'
                            <span style="background: rgba(255,255,255,0.25); color: #fff; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; margin-top: 10px; align-self: flex-start; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.35);">
                                <span class="tv2-check">✓</span> {{ ucfirst($artOrder->status) }}
                            </span>
HTML;
    $artNew = <<<'HTML'
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; margin-top: 10px; padding-right: 4px;">
                                <span style="background: rgba(255,255,255,0.25); color: #fff; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.35);">
                                    <span class="tv2-check">✓</span> {{ ucfirst($artOrder->status) }}
                                </span>
                                <a href="{{ route('traveler.artcraft.claim.show', $artOrder->id) }}" style="background: rgba(255,255,255,0.95); color: #1e293b; padding: 5px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; text-decoration: none;">View</a>
                            </div>
HTML;
    if (str_contains($mt, trim($artOld))) {
        $mt = str_replace(trim($artOld), trim($artNew), $mt);
        echo "  ✅ ArtCraft View button added\n";
    }
}

file_put_contents($myTrips, $mt);

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ ALL DONE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
echo "  php artisan route:clear\n";
echo "  php artisan route:list | findstr claim.show\n";