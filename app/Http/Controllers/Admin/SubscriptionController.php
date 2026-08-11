<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with(['provider', 'plan'])
            ->latest()
            ->paginate(20);
        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['provider', 'plan', 'payments']);
        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function updateStatus(Request $request, Subscription $subscription)
    {
        $request->validate([
            'status' => 'required|in:active,expired,cancelled,pending',
        ]);

        $subscription->status = $request->status;
        $subscription->save();

        return back()->with('success', 'Subscription status updated.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription deleted.');
    }
}