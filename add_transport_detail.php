<?php
/**
 * ADD — Transport Claim Detail Page
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  ADD — TRANSPORT CLAIM DETAIL PAGE\n";
echo "═══════════════════════════════════════════════════════\n\n";

// ═══════════════════════════════════════════════════════
// 1. Add show() method to TransportClaimController
// ═══════════════════════════════════════════════════════
$ctrl = 'app/Http/Controllers/Traveler/TransportClaimController.php';
copy($ctrl, $ctrl . '.bak_detail');
$c = file_get_contents($ctrl);

if (str_contains($c, 'public function show(')) {
    echo "  ⏭ show() already exists in controller\n";
} else {
    $showMethod = <<<'PHP'

    /**
     * Show detail of a claimed transport booking.
     */
    public function show($booking)
    {
        $user = Auth::user();
        
        $claim = \App\Models\BookingClaim::where('claimable_type', \App\Models\TransportBooking::class)
            ->where('claimable_id', $booking)
            ->where('traveler_id', $user->id)
            ->first();
        
        if (!$claim) {
            abort(404, 'Booking not found or not claimed.');
        }
        
        $booking = \App\Models\TransportBooking::with(['provider', 'vehicle'])->findOrFail($booking);
        
        return view('traveler.transport.claim-show', [
            'booking' => $booking,
            'claim' => $claim,
        ]);
    }
}
PHP;

    $lastBracePos = strrpos($c, '}');
    if ($lastBracePos !== false) {
        $c = substr($c, 0, $lastBracePos) . $showMethod . substr($c, $lastBracePos + 1);
    }
    
    file_put_contents($ctrl, $c);
    echo "  ✅ Added show() method to controller\n";
}

// ═══════════════════════════════════════════════════════
// 2. Add Route
// ═══════════════════════════════════════════════════════
$routesFile = 'routes/web.php';
copy($routesFile, $routesFile . '.bak_transport_detail');
$r = file_get_contents($routesFile);

if (str_contains($r, "traveler.transport.claim.show") || str_contains($r, "TransportClaimController::class, 'show'")) {
    echo "  ⏭ Route already exists\n";
} else {
    // Find Transport claim group: look for the verify route
    $oldLine = <<<'ROUTE'
        Route::post('/claim/verify/{token}', [\App\Http\Controllers\Traveler\TransportClaimController::class, 'verify'])
            ->name('claim.verify');
ROUTE;
    
    $newLine = <<<'ROUTE'
        Route::post('/claim/verify/{token}', [\App\Http\Controllers\Traveler\TransportClaimController::class, 'verify'])
            ->name('claim.verify');
        Route::get('/claim/show/{booking}', [\App\Http\Controllers\Traveler\TransportClaimController::class, 'show'])
            ->name('claim.show');
ROUTE;
    
    if (str_contains($r, trim($oldLine))) {
        $r = str_replace(trim($oldLine), trim($newLine), $r);
        file_put_contents($routesFile, $r);
        echo "  ✅ Added route traveler.transport.claim.show\n";
    } else {
        // Fallback: find Transport verify line with single quotes
        $fallbackOld = "Route::post('/claim/verify/{token}', [\\App\\Http\\Controllers\\Traveler\\TransportClaimController::class, 'verify'])";
        if (str_contains($r, $fallbackOld)) {
            $newRouteLine = "\n        Route::get('/claim/show/{booking}', [\\App\\Http\\Controllers\\Traveler\\TransportClaimController::class, 'show'])->name('claim.show');";
            // Find the line and add after the ->name('claim.verify');
            $pos = strpos($r, $fallbackOld);
            $endPos = strpos($r, ';', $pos);
            if ($endPos !== false) {
                $r = substr($r, 0, $endPos + 1) . $newRouteLine . substr($r, $endPos + 1);
                file_put_contents($routesFile, $r);
                echo "  ✅ Added route (fallback).\n";
            }
        } else {
            echo "  ⚠️ Could not find route pattern. Please add manually:\n";
            echo "     Route::get('/claim/show/{booking}', [\\App\\Http\\Controllers\\Traveler\\TransportClaimController::class, 'show'])->name('claim.show');\n";
        }
    }
}

// ═══════════════════════════════════════════════════════
// 3. Create View
// ═══════════════════════════════════════════════════════
$viewPath = 'resources/views/traveler/transport/claim-show.blade.php';
$viewDir = dirname($viewPath);
if (!is_dir($viewDir)) mkdir($viewDir, 0755, true);

$viewContent = <<<'BLADE'
@extends('layouts.traveler-v2')

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    
    <a href="{{ route('traveler.dashboard-v2') }}" style="display: inline-flex; align-items: center; gap: 8px; color: #2563eb; font-weight: 600; text-decoration: none; margin-bottom: 20px; font-size: 14px;">
        ← {{ __('messages.back') ?? 'Back to Dashboard' }}
    </a>

    {{-- Header Card --}}
    <div style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); border-radius: 18px; padding: 24px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-size: 13px; font-weight: 700; color: #1e40af; text-transform: uppercase; letter-spacing: 0.5px;">🚐 {{ __('messages.transport_booking') ?? 'Transport Booking' }}</div>
                <h1 style="font-size: 24px; font-weight: 800; color: #111827; margin: 6px 0;">{{ $booking->booking_ref }}</h1>
                @if($booking->travel_date)
                <p style="color: #1e40af; font-size: 14px; margin: 0;">
                    📅 {{ \Carbon\Carbon::parse($booking->travel_date)->translatedFormat('M d, Y') }}
                </p>
                @endif
            </div>
            <span style="background: rgba(255,255,255,0.7); color: #1e40af; padding: 6px 16px; border-radius: 999px; font-size: 13px; font-weight: 700; border: 1px solid rgba(30, 64, 175, 0.2);">
                {{ ucfirst($booking->status) }}
            </span>
        </div>
    </div>

    {{-- Trip Details --}}
    <div style="background: #fff; border-radius: 16px; padding: 20px; margin-bottom: 16px; border: 1px solid #e5e7eb;">
        <h2 style="font-size: 16px; font-weight: 800; color: #111827; margin: 0 0 14px;">🎫 {{ __('messages.trip_details') ?? 'Trip Details' }}</h2>
        <div style="display: grid; gap: 12px;">
            @if($booking->provider)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                <span style="font-size: 13px; color: #6b7280; font-weight: 600;">{{ __('messages.provider') ?? 'Provider' }}</span>
                <span style="font-size: 14px; color: #111827; font-weight: 700;">{{ $booking->provider->name ?? 'N/A' }}</span>
            </div>
            @endif
            @if($booking->route_name)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                <span style="font-size: 13px; color: #6b7280; font-weight: 600;">{{ __('messages.route') ?? 'Route' }}</span>
                <span style="font-size: 14px; color: #111827; font-weight: 700;">{{ $booking->route_name }}</span>
            </div>
            @endif
            @if($booking->vehicle)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                <span style="font-size: 13px; color: #6b7280; font-weight: 600;">{{ __('messages.vehicle') ?? 'Vehicle' }}</span>
                <span style="font-size: 14px; color: #111827; font-weight: 700;">{{ $booking->vehicle->name ?? $booking->vehicle->type ?? 'N/A' }}</span>
            </div>
            @endif
            @if($booking->passenger_name)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                <span style="font-size: 13px; color: #6b7280; font-weight: 600;">{{ __('messages.passenger') ?? 'Passenger' }}</span>
                <span style="font-size: 14px; color: #111827; font-weight: 700;">{{ $booking->passenger_name }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment --}}
    <div style="background: #fff; border-radius: 16px; padding: 20px; margin-bottom: 16px; border: 1px solid #e5e7eb;">
        <h2 style="font-size: 16px; font-weight: 800; color: #111827; margin: 0 0 14px;">💰 {{ __('messages.payment') ?? 'Payment' }}</h2>
        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-top: 2px solid #e5e7eb; margin-top: 6px;">
            <span style="font-size: 16px; font-weight: 800; color: #111827;">{{ __('messages.total') ?? 'Total Amount' }}</span>
            <span style="font-size: 18px; font-weight: 800; color: #059669;">NPR {{ number_format($booking->amount ?? 0, 2) }}</span>
        </div>
    </div>

    {{-- Claim Info --}}
    <div style="background: #ecfdf5; border-radius: 16px; padding: 16px; margin-bottom: 20px; border: 1px solid #a7f3d0;">
        <p style="font-size: 13px; color: #065f46; margin: 0; font-weight: 600;">
            ✅ {{ __('messages.claimed_on') ?? 'Claimed on' }}: {{ $claim->claimed_at ? $claim->claimed_at->translatedFormat('M d, Y · h:i A') : 'N/A' }}
        </p>
    </div>

    {{-- Back Button --}}
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="{{ route('traveler.dashboard-v2') }}" style="flex: 1; min-width: 140px; text-align: center; background: #2563eb; color: #fff; padding: 12px 20px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px;">
            {{ __('messages.back_to_dashboard') ?? 'Back to Dashboard' }}
        </a>
    </div>

</div>
@endsection
BLADE;

if (file_exists($viewPath)) {
    echo "  ⏭ View already exists: {$viewPath}\n";
} else {
    file_put_contents($viewPath, $viewContent);
    echo "  ✅ Created view: {$viewPath}\n";
}

// ═══════════════════════════════════════════════════════
// 4. Add "View" button to Transport card
// ═══════════════════════════════════════════════════════
$myTrips = 'resources/views/traveler/_partials/my-trips.blade.php';
copy($myTrips, $myTrips . '.bak_transport_btn');
$mt = file_get_contents($myTrips);

if (str_contains($mt, 'transport.claim.show')) {
    echo "  ⏭ View button already added\n";
} else {
    // Exact pattern from L184-186 (Transport status badge)
    $oldPattern = <<<'HTML'
                            <span style="background: rgba(255,255,255,0.25); color: #fff; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; margin-top: 10px; align-self: flex-start; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.35);">
                                <span class="tv2-check">✓</span> {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═══ Claimed Hotel Bookings ═══ --}}
HTML;

    $newPattern = <<<'HTML'
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; margin-top: 10px; padding-right: 4px;">
                                <span style="background: rgba(255,255,255,0.25); color: #fff; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.35);">
                                    <span class="tv2-check">✓</span> {{ ucfirst($booking->status) }}
                                </span>
                                <a href="{{ route('traveler.transport.claim.show', $booking->id) }}" style="background: rgba(255,255,255,0.95); color: #1e293b; padding: 5px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; text-decoration: none;">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═══ Claimed Hotel Bookings ═══ --}}
HTML;

    if (str_contains($mt, trim($oldPattern))) {
        $mt = str_replace(trim($oldPattern), trim($newPattern), $mt);
        file_put_contents($myTrips, $mt);
        echo "  ✅ Added 'View' button to Transport cards\n";
    } else {
        echo "  ⚠️ Pattern not matched exactly. Trying alternate approach...\n";
        // Alt: find the unique transport booking_ref context
        $altPattern = '{{ ucfirst($booking->status) }}';
        $altReplace = <<<'HTML'
{{ ucfirst($booking->status) }}
                                </span>
                                <a href="{{ route('traveler.transport.claim.show', $booking->id) }}" style="background: rgba(255,255,255,0.95); color: #1e293b; padding: 5px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; text-decoration: none; margin-left: 8px;">View</a>
HTML;
        // Only replace first occurrence (transport section - hotel also has $hotelBooking so $booking only in transport)
        if (str_contains($mt, 'transport.claim.show') === false) {
            $pos = strpos($mt, $altPattern);
            if ($pos !== false) {
                $mt = substr_replace($mt, $altReplace, $pos, strlen($altPattern));
                file_put_contents($myTrips, $mt);
                echo "  ✅ Added View (alternate method)\n";
            } else {
                echo "  ❌ Manual intervention required\n";
            }
        }
    }
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ DONE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
echo "  php artisan route:clear\n";
echo "  php artisan route:list | findstr transport.claim\n";