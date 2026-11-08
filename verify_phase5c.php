<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "═══════════════════════════════════════════════════════\n";
echo "  VERIFY — PHASE 5C\n";
echo "═══════════════════════════════════════════════════════\n\n";

// 1. Claim exists?
echo "─── [1] BookingClaim for TX 33 ───\n";
$claim = \App\Models\BookingClaim::where('claimable_type', \App\Models\ExchangeTransaction::class)
    ->where('claimable_id', 33)->first();
if ($claim) {
    echo "  ✅ Claim ID: {$claim->id}\n";
    echo "  Traveler ID: {$claim->traveler_id}\n";
    echo "  Claim Email: {$claim->claim_email}\n";
    echo "  Claimed At: {$claim->claimed_at}\n";
} else {
    echo "  ❌ Not found\n";
}

// 2. Event exists?
echo "\n─── [2] BookingClaimEvent for TX 33 ───\n";
$event = \App\Models\BookingClaimEvent::where('claimable_type', \App\Models\ExchangeTransaction::class)
    ->where('claimable_id', 33)->first();
if ($event) {
    echo "  ✅ Event ID: {$event->id}\n";
    echo "  booking_claim_id: {$event->booking_claim_id}\n";
    echo "  event_type: {$event->event_type}\n";
    echo "  ip_address: {$event->ip_address}\n";
    echo "  created_at: {$event->created_at}\n";
    echo "  metadata:\n";
    echo "    " . json_encode($event->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} else {
    echo "  ❌ Not found\n";
}

// 3. Traveler dashboard will show
echo "\n─── [3] Traveler 299 claimed MC count ───\n";
$count = \App\Models\BookingClaim::where('traveler_id', 299)
    ->where('claimable_type', \App\Models\ExchangeTransaction::class)->count();
echo "  John Anderson claims: {$count}\n";

// 4. Total unclaimed
echo "\n─── [4] Unclaimed Totals ───\n";
$categories = [
    'MC' => [\App\Models\ExchangeTransaction::class, 'exchange_transactions'],
    'Individual' => [\App\Models\IndividualBooking::class, 'individual_bookings'],
    'Hotel' => [\App\Models\HotelBooking::class, 'hotel_bookings'],
    'ArtCraft' => [\App\Models\CustomOrder::class, 'custom_orders'],
    'Restaurant' => [\App\Models\RestaurantOrder::class, 'restaurant_orders'],
    'Transport' => [\App\Models\TransportBooking::class, 'transport_bookings'],
];
$total = 0;
foreach ($categories as $name => $data) {
    $claimedIds = \App\Models\BookingClaim::where('claimable_type', $data[0])->pluck('claimable_id')->toArray();
    $unclaimed = \Illuminate\Support\Facades\DB::table($data[1])->whereNotIn('id', $claimedIds)->count();
    echo "  {$name}: {$unclaimed} unclaimed\n";
    $total += $unclaimed;
}
echo "  Total: {$total}\n";