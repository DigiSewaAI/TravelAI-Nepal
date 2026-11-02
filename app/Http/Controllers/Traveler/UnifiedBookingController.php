<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Order;
use App\Models\WholesaleRfq;
use Illuminate\Support\Facades\Auth;

class UnifiedBookingController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $treks = Booking::where('traveler_id', $userId)->with('service')->latest()->get();
        $orders = Order::where('user_id', $userId)->with('items')->latest()->get();

        $shopOrders      = $orders->filter(fn($o) => $o->items->contains(fn($i) => $i->product_type === 'shop'));
        $rentalOrders    = $orders->filter(fn($o) => $o->items->contains(fn($i) => $i->product_type === 'rental'));
        $wholesaleOrders = $orders->filter(fn($o) => $o->items->contains(fn($i) => $i->product_type === 'wholesale'));

        $rfqs = WholesaleRfq::where('buyer_id', $userId)->with('product', 'provider')->latest()->get();

        $tab = request('tab', 'all');
        if (!in_array($tab, ['all','treks','shop','rental','wholesale','rfqs'], true)) $tab = 'all';

        $counts = [
            'all'       => $treks->count() + $orders->count() + $rfqs->count(),
            'treks'     => $treks->count(),
            'shop'      => $shopOrders->count(),
            'rental'    => $rentalOrders->count(),
            'wholesale' => $wholesaleOrders->count(),
            'rfqs'      => $rfqs->count(),
        ];

        return view('traveler.my-bookings.index', compact(
            'treks', 'shopOrders', 'rentalOrders', 'wholesaleOrders', 'rfqs', 'tab', 'counts'
        ));
    }
}