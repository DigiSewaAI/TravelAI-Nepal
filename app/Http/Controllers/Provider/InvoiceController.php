<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request)
    {
        $provider = auth()->user()->provider;

        $query = Invoice::where('provider_id', $provider->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(15);

        $stats = [
            'total' => Invoice::where('provider_id', $provider->id)->count(),
            'paid' => Invoice::where('provider_id', $provider->id)->where('status', 'paid')->count(),
            'pending' => Invoice::where('provider_id', $provider->id)->where('status', 'pending')->count(),
        ];

        return view('provider.invoices.index', compact('invoices', 'stats'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load(['subscription', 'booking']);
        return view('provider.invoices.show', compact('invoice'));
    }

    public function download(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $pdf = $this->invoiceService->generatePdf($invoice);
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }
}