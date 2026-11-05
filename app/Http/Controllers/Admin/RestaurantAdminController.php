<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingClaim;
use App\Models\BookingClaimEvent;
use App\Models\RestaurantOrder;
use App\Services\Claim\ReceiptCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RestaurantAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = RestaurantOrder::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderByDesc('id')->paginate(20)->appends(['search' => $search]);
        return view('admin.restaurant.orders.index', compact('orders', 'search'));
    }

    public function showReissue(RestaurantOrder $order)
    {
        // Guard: already claimed?
        $claimed = BookingClaim::where('claimable_type', RestaurantOrder::class)
            ->where('claimable_id', $order->id)
            ->exists();

        return view('admin.restaurant.orders.reissue', compact('order', 'claimed'));
    }

    public function reissue(Request $request, RestaurantOrder $order)
    {
        if (!config('claim.restaurant_enabled')) {
            return back()->with('error', __('messages.admin_restaurant_reissue_config'));
        }

        try {
            $service = app(ReceiptCodeService::class);
        } catch (\RuntimeException $e) {
            return back()->with('error', __('messages.admin_restaurant_reissue_config'));
        }

        // Guard: already claimed?
        if (BookingClaim::where('claimable_type', RestaurantOrder::class)->where('claimable_id', $order->id)->exists()) {
            return back()->with('error', __('messages.admin_restaurant_reissue_claimed'));
        }

        // Guard: max reissues (3 absolute, 5 within 24h)
        $within24h = BookingClaimEvent::where('event_type', 'receipt_reissued')
            ->where('claimable_type', RestaurantOrder::class)
            ->where('claimable_id', $order->id)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $max = $within24h >= 3 ? 5 : 3;
        if ((int) $order->receipt_code_reissue_count >= $max) {
            return back()->with('error', __('messages.admin_restaurant_reissue_max'));
        }

        $plainCode = null;

        try {
            $plainCode = DB::transaction(function () use ($order, $service) {
                $plain = $service->generate();
                $order->update([
                    'receipt_code_hash' => $service->hash($plain),
                    'receipt_code_issued_at' => now(),
                    'receipt_code_used_at' => null,
                    'receipt_code_reissue_count' => (int) $order->receipt_code_reissue_count + 1,
                ]);

                BookingClaimEvent::create([
                    'traveler_id' => Auth::id(),
                    'event_type' => 'receipt_reissued',
                    'claimable_type' => RestaurantOrder::class,
                    'claimable_id' => $order->id,
                    'metadata' => [
                        'reissue_count' => (int) $order->receipt_code_reissue_count,
                        'within_24h' => true,
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => Str::limit(request()->userAgent() ?? '', 250),
                    'created_at' => now(),
                ]);

                return $plain;
            });
        } catch (\Exception $e) {
            \Log::error('Restaurant reissue failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return back()->with('error', __('messages.admin_restaurant_reissue_config'));
        }

        // Stream PDF
        try {
            $provider = $order->provider ?? null;
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('provider.restaurant.receipt-pdf', [
                'order' => $order,
                'plainCode' => $plainCode,
                'provider' => $provider,
            ]);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'receipt-reissue-' . $order->order_number . '.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            \Log::error('Reissue PDF failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return back()->with('error', __('messages.admin_restaurant_reissue_config'));
        }
    }
}