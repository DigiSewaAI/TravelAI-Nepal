<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Resolve vertical-specific layout based on provider type.
     */
    private function getProviderLayout($provider): string
    {
        $map = [
            'restaurant'         => 'layouts.provider-restaurant',
            'hotel'              => 'layouts.provider-hotel',
            'resort'             => 'layouts.provider-hotel',
            'lodge'              => 'layouts.provider-hotel',
            'homestay'           => 'layouts.provider-hotel',
            'transport-provider' => 'layouts.provider-transport',
            'shop-owner'         => 'layouts.provider-inventory',
            'rental-provider'    => 'layouts.provider-inventory',
            'wholesale-provider' => 'layouts.provider-inventory',
            'art-craft'          => 'layouts.provider-art-craft',
            'guide'              => 'layouts.provider-individual',
            'porter'             => 'layouts.provider-individual',
            'photographer'       => 'layouts.provider-individual',
            'money-changer'      => 'layouts.provider-money-changer',
        ];
        return $map[$provider?->types->first()?->slug] ?? 'layouts.provider';
    }
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

        $provider_layout = $this->getProviderLayout($provider);
return view(($provider_layout === 'layouts.provider') ? 'provider.invoices.index' : 'provider.invoices.index-premium', compact('invoices', 'stats', 'provider_layout'));
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

        $provider_layout = $this->getProviderLayout($provider);
return view(($provider_layout === 'layouts.provider') ? 'provider.invoices.show' : 'provider.invoices.show-premium', compact('invoice', 'provider_layout'));
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
            \Log::error('Invoice PDF generation failed', ['invoice_id' => $invoice->id, 'error_class' => get_class($e)]);
            return redirect()->back()->with('error', 'PDF generation failed. Please try again.');
        }
    }
}