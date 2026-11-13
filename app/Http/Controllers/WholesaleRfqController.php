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

        // Self-RFQ guard: provider cannot request quote on own product
        $__authProvider = auth()->user()->getCurrentProvider();
        if ($__authProvider && (int)$__authProvider->id === (int)$product->provider_id) {
            return back()->with('error', 'You cannot request a quote for your own product.');
        }

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

        // Notify admins: new RFQ
        try {
            $__adminUrl = \Illuminate\Support\Facades\Route::has('admin.providers.show')
                ? route('admin.providers.show', $product->provider_id)
                : url('/admin/dashboard');
            $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AdminAlertNotification(
                    'New Wholesale RFQ',
                    (auth()->user()->name ?? 'Buyer') . ' requested quote for ' . ($product->name ?? 'Product') . ' (Qty ' . ($validated['quantity'] ?? 0) . ')',
                    'rfq',
                    $__adminUrl,
                    $rfq->id
                ));
            }
        } catch (\Throwable $e) {
            \Log::warning('Admin RFQ notify failed', ['rfq_id' => $rfq->id, 'error' => $e->getMessage()]);
        }

        $providerIds = $product->provider_id ? [(int)$product->provider_id] : [];
        $__prodName = $product->name ?? 'Product';
        $__qty = $validated['quantity'] ?? 0;
        try {
            if (!empty($providerIds)) {
                foreach ($providerIds as $pid) {
                    $ownerId = \App\Models\Provider::where('id', $pid)->value('user_id');
                    if (!$ownerId) continue;
                    $owner = \App\Models\User::find($ownerId);
                    if ($owner && method_exists($owner, 'notify')) {
                        // Self-notify guard
        if ($owner && $owner->id !== auth()->id()) {
            $owner->notify(new \App\Notifications\ProviderAlertNotification(
                            title: 'New Wholesale RFQ',
                            message: $__prodName . ' — Qty ' . $__qty,
                            category: 'wholesale_rfq',
                            url: route('provider.wholesale-rfq.show', $rfq)
                        ));
        }
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Notify failed', ['ctx' => 'wholesale_rfq', 'e' => $e->getMessage()]);
        }

        // 📧 Buyer confirmation — bell (database only) + branded email
        try {
            $__buyer = $rfq->buyer;
            if ($__buyer) {
                // Bell notification — database only (skip mail to avoid duplicate)
                \DB::table('notifications')->insert([
                    'id'              => (string) \Illuminate\Support\Str::uuid(),
                    'type'            => 'App\Notifications\UserAlertNotification',
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id'   => $__buyer->id,
                    'data'            => json_encode([
                        'type'     => 'user_alert',
                        'category' => 'rfq',
                        'title'    => 'RFQ Submitted',
                        'message'  => 'Your RFQ ' . $rfq->rfq_number . ' for ' . $__prodName . ' has been submitted.',
                        'url'      => route('wholesale.rfq.show', $rfq),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Branded confirmation email
                if (!empty($__buyer->email)) {
                    \Mail::to($__buyer->email)->send(new \App\Mail\WholesaleRfqMail($rfq, 'confirmation', 'buyer'));
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('RFQ buyer confirmation failed', ['rfq' => $rfq->id, 'e' => $e->getMessage()]);
        }

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

        // Notify provider (bell + email)
        try {
            $providerUser = $rfq->provider?->user;
            if ($providerUser) {
                // Self-notify guard
        if ($providerUser && $providerUser->id !== auth()->id()) {
            $providerUser->notify(new \App\Notifications\ProviderAlertNotification(
                    title: 'New RFQ Message',
                    message: 'New message on ' . $rfq->rfq_number . ': ' . \Illuminate\Support\Str::limit($validated['message'], 100),
                    category: 'wholesale_rfq',
                    url: route('provider.wholesale-rfq.show', $rfq)
                ));
        }
                if ($providerUser->email) {
                    \Mail::to($providerUser->email)->send(new \App\Mail\WholesaleRfqMail($rfq, 'message', 'provider', $validated['message']));
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('RFQ message notification failed', ['rfq' => $rfq->id, 'e' => $e->getMessage()]);
        }

        return back()->with('success', __('messages.rfq_message_sent'));
    }

    public function accept(WholesaleRfq $rfq)
    {
        if ($rfq->buyer_id !== auth()->id()) abort(403);
        if (!$rfq->isQuoted()) abort(403);

        $rfq->update(['status' => 'accepted', 'accepted_at' => now()]);

        try {
            $providerUser = $rfq->provider?->user;
            if ($providerUser) {
                // Self-notify guard
        if ($providerUser && $providerUser->id !== auth()->id()) {
            $providerUser->notify(new \App\Notifications\ProviderAlertNotification('Quote Accepted', 'Buyer accepted your quote for '.$rfq->rfq_number.' (NPR '.number_format($rfq->quoted_total, 2).')', 'rfq', route('provider.wholesale-rfq.show', $rfq)));
        }
                if ($providerUser->email) {
                    \Mail::to($providerUser->email)->send(new \App\Mail\WholesaleRfqMail($rfq, 'accepted', 'provider'));
                }
            }
        } catch (\Throwable $e) { \Log::warning('RFQ accept notification failed', ['rfq' => $rfq->id, 'e' => $e->getMessage()]); }

        return back()->with('success', __('messages.rfq_accepted'));
    }

    public function reject(WholesaleRfq $rfq)
    {
        if ($rfq->buyer_id !== auth()->id()) abort(403);
        if (!$rfq->isQuoted()) abort(403);

        $rfq->update(['status' => 'rejected', 'rejected_at' => now()]);

        try {
            $providerUser = $rfq->provider?->user;
            if ($providerUser) {
                // Self-notify guard
        if ($providerUser && $providerUser->id !== auth()->id()) {
            $providerUser->notify(new \App\Notifications\ProviderAlertNotification('Quote Rejected', 'Buyer rejected your quote for '.$rfq->rfq_number, 'rfq', route('provider.wholesale-rfq.show', $rfq)));
        }
                if ($providerUser->email) {
                    \Mail::to($providerUser->email)->send(new \App\Mail\WholesaleRfqMail($rfq, 'rejected', 'provider'));
                }
            }
        } catch (\Throwable $e) { \Log::warning('RFQ reject notification failed', ['rfq' => $rfq->id, 'e' => $e->getMessage()]); }

        return back()->with('success', __('messages.rfq_rejected'));
    }
}