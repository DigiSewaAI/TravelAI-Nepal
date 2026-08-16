<?php

namespace App\Http\Controllers\Admin;

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
        $query = Invoice::with(['provider', 'subscription']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('invoice_number', 'LIKE', '%' . $request->search . '%')
                  ->orWhereHas('provider', function($q) use ($request) {
                      $q->where('name', 'LIKE', '%' . $request->search . '%');
                  });
        }

        $invoices = $query->latest()->paginate(20);

        $stats = [
            'total' => Invoice::count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'pending' => Invoice::where('status', 'pending')->count(),
            'overdue' => Invoice::where('status', 'overdue')->count(),
        ];

        return view('admin.invoices.index', compact('invoices', 'stats'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['provider', 'subscription', 'booking']);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function download(Invoice $invoice)
    {
        $pdf = $this->invoiceService->generatePdf($invoice);
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,overdue,cancelled'
        ]);

        $invoice->update(['status' => $request->status]);
        return back()->with('success', 'Invoice status updated successfully.');
    }
}