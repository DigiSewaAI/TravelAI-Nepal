<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // ✅ hasOne relationship प्रयोग गर्नुहोस्
        $provider = $user->provider;

        // 🔥 Fallback – यदि relationship काम गरेन भने direct query
        if (!$provider) {
            $provider = \App\Models\Provider::where('user_id', $user->id)->first();
        }

        if (!$provider) {
            return redirect()->route('provider.dashboard')
                ->with('error', 'Provider profile not found. Please contact support.');
        }

        $query = Invoice::where('provider_id', $provider->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(15);

        $stats = [
            'total' => Invoice::where('provider_id', $provider->id)->count(),
            'paid' => Invoice::where('provider_id', $provider->id)->where('status', 'paid')->count(),
            'pending' => Invoice::where('provider_id', $provider->id)->where('status', 'pending')->count(),
            'overdue' => Invoice::where('provider_id', $provider->id)->where('status', 'overdue')->count(),
        ];

        return view('provider.invoices.index', compact('invoices', 'stats'));
    }

    public function show(Invoice $invoice)
    {
        $user = Auth::user();
        $provider = $user->provider;

        if (!$provider) {
            $provider = \App\Models\Provider::where('user_id', $user->id)->first();
        }

        if (!$provider || $invoice->provider_id !== $provider->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        return view('provider.invoices.show', compact('invoice'));
    }

    public function download(Invoice $invoice)
    {
        $user = Auth::user();
        $provider = $user->provider;

        if (!$provider) {
            $provider = \App\Models\Provider::where('user_id', $user->id)->first();
        }

        if (!$provider || $invoice->provider_id !== $provider->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        // ✅ PDF generation – DomPDF प्रयोग गर्नुहोस्
        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('provider.invoices.pdf', compact('invoice'));
            return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'PDF generation failed: ' . $e->getMessage());
        }
    }
}