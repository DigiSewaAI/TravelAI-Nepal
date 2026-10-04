<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\WholesaleRfq;
use App\Models\WholesaleRfqMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WholesaleRfqController extends Controller
{
    private function getProvider()
    {
        $p = Auth::user()->ownProvider();
        if (!$p) abort(403);
        return $p;
    }

    public function index(Request $request)
    {
        $provider = $this->getProvider();
        $status = $request->query('status', 'all');

        $rfqs = WholesaleRfq::forProvider($provider->id)
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->with(['product', 'buyer'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all'      => WholesaleRfq::forProvider($provider->id)->count(),
            'pending'  => WholesaleRfq::forProvider($provider->id)->pending()->count(),
            'quoted'   => WholesaleRfq::forProvider($provider->id)->quoted()->count(),
            'accepted' => WholesaleRfq::forProvider($provider->id)->where('status', 'accepted')->count(),
        ];

        return view('provider.wholesale-rfq.index', compact('rfqs', 'status', 'counts'));
    }

    public function show(WholesaleRfq $rfq)
    {
        $provider = $this->getProvider();
        if ($rfq->provider_id !== $provider->id) abort(403);
        $rfq->load(['product', 'buyer', 'messages.sender']);
        return view('provider.wholesale-rfq.show', compact('rfq'));
    }

    public function quote(Request $request, WholesaleRfq $rfq)
    {
        $provider = $this->getProvider();
        if ($rfq->provider_id !== $provider->id) abort(403);

        $validated = $request->validate([
            'quoted_price'      => 'required|numeric|min:0',
            'provider_response' => 'nullable|string|max:2000',
            'valid_until'       => 'nullable|date|after:today',
        ]);

        $rfq->update([
            'quoted_price'      => $validated['quoted_price'],
            'quoted_total'      => $validated['quoted_price'] * $rfq->quantity,
            'provider_response' => $validated['provider_response'] ?? null,
            'valid_until'       => $validated['valid_until'] ?? now()->addDays(7)->toDateString(),
            'status'            => 'quoted',
            'responded_at'      => now(),
        ]);

        return back()->with('success', __('messages.rfq_quoted'));
    }

    public function sendMessage(Request $request, WholesaleRfq $rfq)
    {
        $provider = $this->getProvider();
        if ($rfq->provider_id !== $provider->id) abort(403);

        $validated = $request->validate(['message' => 'required|string|max:2000']);

        WholesaleRfqMessage::create([
            'rfq_id'      => $rfq->id,
            'sender_id'   => auth()->id(),
            'sender_role' => 'provider',
            'message'     => $validated['message'],
        ]);

        return back()->with('success', __('messages.rfq_message_sent'));
    }
}