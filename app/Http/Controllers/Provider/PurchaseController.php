<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) abort(403);

        $orders = Order::where('user_id', $user->id)
            ->with(['items'])
            ->latest()
            ->paginate(20);

        return view('provider.purchases.index', compact('orders'));
    }
}