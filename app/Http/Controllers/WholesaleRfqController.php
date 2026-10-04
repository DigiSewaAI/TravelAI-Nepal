<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WholesaleRfq;
use App\Models\WholesaleRfqMessage;
use Illuminate\Http\Request;

class WholesaleRfqController extends Controller
{
    public function create(Product $product)
    {
        if (!$product->isWholesale()) abort(404);
        if ($product->status !== 'active') abort(404);

        return view('wholesale.rfq.create', compact('product'));
    }

    public function store(Request $request, Product $product)
    {
        if (!$product->isWholesale()) abort(404);

        $minQty = $product->wholesaleDetail->min_order_qty ?? 1;

        $validated = $request->validate([
            'quantity'      => "required|integer|min:{$minQty}",
            'message'       => 'nullable|string|max:2000',
            'buyer_company' => 'nullable|string|max:150',
            'buyer_phone'   => 'nullable|string|max:20',
        ]);

        $rfq = WholesaleRfq::create([
            'buyer_id'      => auth()->id(),
            'provider_id'   => $product->provider_id,
            'product_id'    => $product->id,
            'quantity'      => $validated['quantity'],
            'message'       => $validated['message'] ?? null,
            'buyer_company' => $validated['buyer_company'] ?? null,
            'buyer_phone'   => $validated['buyer_phone'] ?? null,
            'status'        => 'pending',
        ]);

        return redirect()->route('wholesale.rfq.show', $rfq)
            ->with('success', __('messages.rfq_created'));
    }

    public function index()
    {
        $rfqs = WholesaleRfq::forBuyer(auth()->id())
            ->with(['product', 'provider'])
            ->latest()
            ->paginate(10);

        return view('wholesale.rfq.index', compact('rfqs'));
    }

    public function show(WholesaleRfq $rfq)
    {
        if ($rfq->buyer_id !== auth()->id()) abort(403);
        $rfq->load(['product', 'provider', 'messages.sender']);
        return view('wholesale.rfq.show', compact('rfq'));
    }

    public function sendMessage(Request $request, WholesaleRfq $rfq)
    {
        if ($rfq->buyer_id !== auth()->id()) abort(403);
        if (in_array($rfq->status, ['expired', 'cancelled', 'rejected'])) {
            return back()->with('error', __('messages.rfq_closed'));
        }

        $validated = $request->validate(['message' => 'required|string|max:2000']);

        WholesaleRfqMessage::create([
            'rfq_id'      => $rfq->id,
            'sender_id'   => auth()->id(),
            'sender_role' => 'buyer',
            'message'     => $validated['message'],
        ]);

        return back()->with('success', __('messages.rfq_message_sent'));
    }

    public function accept(WholesaleRfq $rfq)
    {
        if ($rfq->buyer_id !== auth()->id()) abort(403);
        if (!$rfq->isQuoted()) abort(403);

        $rfq->update(['status' => 'accepted', 'accepted_at' => now()]);

        return back()->with('success', __('messages.rfq_accepted'));
    }

    public function reject(WholesaleRfq $rfq)
    {
        if ($rfq->buyer_id !== auth()->id()) abort(403);
        if (!$rfq->isQuoted()) abort(403);

        $rfq->update(['status' => 'rejected', 'rejected_at' => now()]);

        return back()->with('success', __('messages.rfq_rejected'));
    }
}