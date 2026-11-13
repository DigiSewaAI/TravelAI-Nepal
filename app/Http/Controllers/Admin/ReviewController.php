<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

// 🔥 Import the notification class
use App\Notifications\NewReviewReceived;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['user', 'service', 'booking'])
            ->latest()
            ->paginate(20);
        return view('admin.reviews.index', compact('reviews'));
    }

    public function show(Review $review)
    {
        $review->load(['user', 'service', 'booking']);
        return view('admin.reviews.show', compact('review'));
    }

    public function approve(Review $review)
    {
        $review->status = 'approved';
        $review->save();

                // 🆕 Send notification to provider owner
        if ($review->service && $review->service->provider && $review->service->provider->user) {
            $review->service->provider->user->notify(new NewReviewReceived($review));
        }

        // 🆕 Notify traveler (approved)
        if ($review->user) {
            $review->user->notify(new \App\Notifications\ReviewApproved($review));
        }

        // 🔥 Optionally notify the traveler that their review is approved
        if ($review->user) {
            // You can create a separate notification like ReviewApproved if needed
            // For now, we'll skip or you can add a custom message
            // $review->user->notify(new ReviewApproved($review));
        }

        return back()->with('success', 'Review approved successfully.');
    }

    public function reject(Request $request, Review $review)
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $review->status = 'rejected';
        $review->rejection_reason = $validated['rejection_reason'] ?? null;
        $review->save();

        // 🆕 Notify traveler
        if ($review->user) {
            $review->user->notify(new \App\Notifications\ReviewRejected($review, $review->rejection_reason));
        }

        // Optionally notify traveler that review was rejected
        // if ($review->user) {
        //     $review->user->notify(new ReviewRejected($review));
        // }

        return back()->with('success', 'Review rejected.');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted.');
    }
}