<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Review;
use App\Services\ReviewEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(protected ReviewEligibilityService $eligibility) {}

    public function index(Request $request)
    {
        $reviews = Review::where('user_id', Auth::id())
            ->with(['service', 'booking', 'order'])
            ->latest()
            ->paginate(10);

        return view('traveler.reviews.index', compact('reviews'));
    }

    /**
     * CREATE — Booking-based
     */
    public function create(Booking $booking)
    {
        $check = $this->eligibility->checkBooking($booking);
        if (!$check['allowed']) {
            return back()->with('error', $check['reason']);
        }
        return view('traveler.reviews.create', compact('booking'));
    }

    /**
     * CREATE — Order-based
     */
    public function createOrder(Order $order)
    {
        $check = $this->eligibility->checkOrder($order);
        if (!$check['allowed']) {
            return back()->with('error', $check['reason']);
        }
        return view('traveler.reviews.create-order', compact('order'));
    }

    /**
     * STORE — Booking-based
     */
    public function store(Request $request, Booking $booking)
    {
        $check = $this->eligibility->checkBooking($booking);
        if (!$check['allowed']) {
            return back()->with('error', $check['reason']);
        }

        $validated = $this->validateReview($request);

        $review = Review::create([
            'booking_id'        => $booking->id,
            'user_id'           => Auth::id(),
            'service_id'        => $booking->service_id,
            'provider_id'       => $booking->service?->provider_id,
            'reviewable_type'   => Booking::class,
            'reviewable_id'     => $booking->id,
            'rating'            => $validated['rating'],
            'title'             => $validated['title'] ?? null,
            'comment'           => $validated['comment'] ?? null,
            'sub_ratings'       => $validated['sub_ratings'] ?? null,
            'photos'            => $validated['photos'] ?? null,
            'verified_purchase' => true, // eligibility verified above
            'status'            => 'pending',
        ]);

        // 🆕 Notify admins (submit हुँदा)
        try {
            $__admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
            foreach ($__admins as $__admin) {
                $__admin->notify(new \App\Notifications\AdminAlertNotification(
                    'New Review Submitted',
                    Auth::user()->name . ' reviewed ' . ($booking->service?->name ?? 'service') . ' (' . $validated['rating'] . '★)',
                    'review',
                    route('admin.reviews.show', $review->id)
                ));
            }
        } catch (\Throwable $e) {
            \Log::warning('Admin review notify failed', ['review_id' => $review->id, 'e' => $e->getMessage()]);
        }

        return redirect()->route('traveler.reviews.index')
            ->with('success', 'Your review has been submitted for approval.');
    }

    /**
     * STORE — Order-based
     */
    public function storeOrder(Request $request, Order $order)
    {
        $check = $this->eligibility->checkOrder($order);
        if (!$check['allowed']) {
            return back()->with('error', $check['reason']);
        }

        $validated = $this->validateReview($request);

        // If single-vendor order, set provider_id
        $uniqueProviders = $order->items->pluck('provider_id')->unique()->filter();
        $singleProvider  = $uniqueProviders->count() === 1 ? $uniqueProviders->first() : null;

        $review = Review::create([
            'order_id'          => $order->id,
            'user_id'           => Auth::id(),
            'provider_id'       => $singleProvider,
            'reviewable_type'   => Order::class,
            'reviewable_id'     => $order->id,
            'rating'            => $validated['rating'],
            'title'             => $validated['title'] ?? null,
            'comment'           => $validated['comment'] ?? null,
            'sub_ratings'       => $validated['sub_ratings'] ?? null,
            'photos'            => $validated['photos'] ?? null,
            'verified_purchase' => true,
            'status'            => 'pending',
        ]);

        // 🆕 Notify admins (submit हुँदा)
        try {
            $__admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
            foreach ($__admins as $__admin) {
                $__admin->notify(new \App\Notifications\AdminAlertNotification(
                    'New Review Submitted',
                    Auth::user()->name . ' reviewed Order #' . $order->order_number . ' (' . $validated['rating'] . '★)',
                    'review',
                    route('admin.reviews.show', $review->id)
                ));
            }
        } catch (\Throwable $e) {
            \Log::warning('Admin review notify failed', ['review_id' => $review->id, 'e' => $e->getMessage()]);
        }

        return redirect()->route('traveler.reviews.index')
            ->with('success', 'Your review has been submitted for approval.');
    }

    /**
     * EDIT — 24h window
     */
    public function edit(Review $review)
    {
        if ($review->user_id !== Auth::id()) abort(403);
        if (!$review->canBeEdited()) {
            return back()->with('error', 'You cannot edit this review anymore (24h window expired or rejected).');
        }
        return view('traveler.reviews.edit', compact('review'));
    }

    public function update(Request $request, Review $review)
    {
        if ($review->user_id !== Auth::id()) abort(403);
        if (!$review->canBeEdited()) {
            return back()->with('error', 'Edit window expired.');
        }

        $validated = $this->validateReview($request);

        $review->update([
            'original_rating' => $review->rating,
            'rating'          => $validated['rating'],
            'title'           => $validated['title'] ?? null,
            'comment'         => $validated['comment'] ?? null,
            'sub_ratings'     => $validated['sub_ratings'] ?? null,
            'photos'          => $validated['photos'] ?? null,
            'edited_at'       => now(),
            'status'          => 'pending', // reset to pending after edit
        ]);

        return redirect()->route('traveler.reviews.index')
            ->with('success', 'Review updated. It will be re-moderated.');
    }

    public function destroy(Review $review)
    {
        if ($review->user_id !== Auth::id()) abort(403);
        if (!$review->canBeDeleted()) {
            return back()->with('error', 'You can only delete pending reviews.');
        }
        $review->delete();
        return redirect()->route('traveler.reviews.index')
            ->with('success', 'Review deleted.');
    }

    /**
     * Shared validation
     */
    protected function validateReview(Request $request): array
    {
        return $request->validate([
            'rating'        => 'required|integer|min:1|max:5',
            'title'         => 'nullable|string|max:150',
            'comment'       => 'nullable|string|max:2000',
            'sub_ratings'   => 'nullable|array',
            'photos'        => 'nullable|array|max:3',
            'photos.*'      => 'image|max:2048',
        ]);
    }
}