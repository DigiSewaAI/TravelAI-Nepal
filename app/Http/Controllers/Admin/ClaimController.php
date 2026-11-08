<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingClaim;
use App\Models\BookingClaimEvent;
use App\Models\CustomOrder;
use App\Models\ExchangeTransaction;
use App\Models\HotelBooking;
use App\Models\IndividualBooking;
use App\Models\RestaurantOrder;
use App\Models\TransportBooking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClaimController extends Controller
{
    /**
     * Supported categories and their models.
     */
    protected function categories(): array
    {
        return [
            'moneychanger' => [
                'model' => ExchangeTransaction::class,
                'label' => 'MoneyChanger',
                'icon' => '💱',
                'ref_field' => 'transaction_number',
                'email_field' => 'customer_email',
                'amount_field' => 'npr_amount',
                'date_field' => 'created_at',
            ],
            'individual' => [
                'model' => IndividualBooking::class,
                'label' => 'Individual',
                'icon' => '🧑‍💼',
                'ref_field' => 'booking_number',
                'email_field' => 'customer_email',
                'amount_field' => 'amount',
                'date_field' => 'start_date',
            ],
            'hotel' => [
                'model' => HotelBooking::class,
                'label' => 'Hotel',
                'icon' => '🏨',
                'ref_field' => 'booking_number',
                'email_field' => 'guest_email',
                'amount_field' => 'amount',
                'date_field' => 'check_in_date',
            ],
            'artcraft' => [
                'model' => CustomOrder::class,
                'label' => 'ArtCraft',
                'icon' => '🎨',
                'ref_field' => 'booking_number',
                'email_field' => 'client_email',
                'amount_field' => 'budget',
                'date_field' => 'created_at',
            ],
            'restaurant' => [
                'model' => RestaurantOrder::class,
                'label' => 'Restaurant',
                'icon' => '🍽️',
                'ref_field' => 'order_number',
                'email_field' => 'customer_email',
                'amount_field' => 'total',
                'date_field' => 'created_at',
            ],
            'transport' => [
                'model' => TransportBooking::class,
                'label' => 'Transport',
                'icon' => '🚐',
                'ref_field' => 'booking_ref',
                'email_field' => 'passenger_email',
                'amount_field' => 'amount',
                'date_field' => 'travel_date',
            ],
        ];
    }

    /**
     * List unclaimed transactions.
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $categories = $this->categories();
        $selectedCategory = $request->get('category', 'all');
        $search = $request->get('search', '');

        // Determine which categories to query
        $activeCategories = $selectedCategory === 'all'
            ? array_keys($categories)
            : [$selectedCategory];

        $allUnclaimed = collect();
        $categoryCounts = [];

        foreach ($activeCategories as $catKey) {
            if (!isset($categories[$catKey])) continue;

            $cat = $categories[$catKey];
            $model = $cat['model'];
            $refField = $cat['ref_field'];
            $emailField = $cat['email_field'];

            // Get claimed IDs
            $claimedIds = BookingClaim::where('claimable_type', $model)
                ->pluck('claimable_id')
                ->toArray();

            // Query unclaimed
            $query = $model::whereNotIn('id', $claimedIds);

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search, $refField, $emailField) {
                    $q->where($refField, 'like', "%{$search}%");
                    if ($emailField) {
                        $q->orWhere($emailField, 'like', "%{$search}%");
                    }
                    // Also search customer name field
                    $nameFields = ['customer_name', 'client_name', 'guest_name', 'passenger_name'];
                    foreach ($nameFields as $nf) {
                        try { $q->orWhere($nf, 'like', "%{$search}%"); } catch (\Exception $e) {}
                    }
                });
            }

            $rows = $query->orderBy('id', 'desc')->limit(100)->get();

            foreach ($rows as $row) {
                $allUnclaimed->push([
                    'category' => $catKey,
                    'category_label' => $cat['label'],
                    'icon' => $cat['icon'],
                    'id' => $row->id,
                    'ref' => $row->{$refField} ?? ('#' . $row->id),
                    'email' => $row->{$emailField} ?? null,
                    'amount' => $row->{$cat['amount_field']} ?? 0,
                    'date' => $row->{$cat['date_field']} ?? null,
                    'customer_name' => $row->customer_name ?? $row->client_name ?? $row->guest_name ?? $row->passenger_name ?? 'N/A',
                ]);
            }
        }

        // Counts per category
        foreach ($categories as $catKey => $cat) {
            $model = $cat['model'];
            $claimedIds = BookingClaim::where('claimable_type', $model)
                ->pluck('claimable_id')
                ->toArray();
            $categoryCounts[$catKey] = $model::whereNotIn('id', $claimedIds)->count();
        }

        return view('admin.claims.index', compact(
            'allUnclaimed',
            'categories',
            'categoryCounts',
            'selectedCategory',
            'search'
        ));
    }

    /**
     * Show single transaction with traveler search.
     */
    public function show($type, $id)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $categories = $this->categories();
        if (!isset($categories[$type])) {
            abort(404, 'Invalid category');
        }

        $cat = $categories[$type];
        $model = $cat['model'];

        $transaction = $model::findOrFail($id);

        // Check if already claimed
        $existingClaim = BookingClaim::where('claimable_type', $model)
            ->where('claimable_id', $id)
            ->first();

        // Get travelers (for search)
        $travelers = User::where('role', 'traveler')
            ->orderBy('name')
            ->limit(50)
            ->get();

        return view('admin.claims.show', compact(
            'transaction',
            'cat',
            'type',
            'existingClaim',
            'travelers'
        ));
    }

    /**
     * Approve manual claim.
     */
    public function approve(Request $request, $type, $id)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'traveler_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $categories = $this->categories();
        if (!isset($categories[$type])) {
            abort(404, 'Invalid category');
        }

        $cat = $categories[$type];
        $model = $cat['model'];

        $transaction = $model::findOrFail($id);
        $traveler = User::findOrFail($validated['traveler_id']);

        // Check if already claimed
        $existing = BookingClaim::where('claimable_type', $model)
            ->where('claimable_id', $id)
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => 'This transaction is already claimed.']);
        }

        $emailField = $cat['email_field'];
        $claimEmail = $transaction->{$emailField} ?? $traveler->email;

        DB::transaction(function () use ($model, $id, $traveler, $claimEmail, $type, $transaction, $validated, $request) {
            // Create claim
            $claim = BookingClaim::create([
                'claimable_type' => $model,
                'claimable_id' => $id,
                'traveler_id' => $traveler->id,
                'claim_email' => $claimEmail,
                'claimed_at' => now(),
            ]);

            // Log event (correct column names)
            BookingClaimEvent::create([
                'booking_claim_id' => $claim->id,
                'booking_claim_attempt_id' => null,
                'claimable_type' => $model,
                'claimable_id' => $id,
                'traveler_id' => $traveler->id,
                'event_type' => 'admin_manual_claim',
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 250),
                'metadata' => [
                    'admin_id' => Auth::id(),
                    'admin_name' => Auth::user()->name,
                    'traveler_email' => $traveler->email,
                    'notes' => $validated['notes'] ?? null,
                    'category' => $type,
                ],
                'created_at' => now(),
            ]);
        });

        return redirect()->route('admin.claims.index')
            ->with('success', "Claim created: {$cat['label']} #{$id} → {$traveler->name} ({$traveler->email})");
    }
}