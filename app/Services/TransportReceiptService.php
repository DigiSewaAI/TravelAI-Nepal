<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\TransportBooking;
use Illuminate\Support\Facades\DB;

class TransportReceiptService
{
    public function create(TransportBooking $booking): Invoice
    {
        // Idempotent — existing receipt भए return
        $existing = Invoice::where('provider_id', $booking->provider_id)
            ->whereJsonContains('metadata->transport_booking_id', $booking->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($booking) {
            $year = date('Y');
            $last = Invoice::where('invoice_number', 'like', 'TR-RCP-' . $year . '-%')
                ->orderByDesc('id')
                ->value('invoice_number');
            $nextSeq = $last ? ((int) substr($last, -4)) + 1 : 1;
            $receiptNumber = 'TR-RCP-' . $year . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            $amount = (float) ($booking->total_amount ?? $booking->amount ?? 0);

            return Invoice::create([
                'provider_id'     => $booking->provider_id,
                'subscription_id' => null,
                'booking_id'      => null,
                'invoice_number'  => $receiptNumber,
                'receipt_number'  => $receiptNumber,
                'amount'          => $amount,
                'currency'        => 'NPR',
                'tax'             => 0,
                'total'           => $amount,
                'status'          => 'paid',
                'payment_method'  => $booking->payment_method ?? 'manual',
                'paid_at'         => $booking->paid_at ?? now(),
                'metadata'        => [
                    'source'              => 'transport_receipt',
                    'transport_booking_id' => $booking->id,
                    'booking_ref'         => $booking->booking_ref,
                    'provider_vat_status' => 'non_registered',
                    'receipt_type'        => 'payment_receipt',
                ],
            ]);
        });
    }
        /**
     * Build brand array for PDF (no size limit — DomPDF handles any logo size).
     * Only shows custom logo if plan has 'custom_logo' feature.
     */
    public function buildBrandForPdf($provider): array
    {
        $brand = [
            'name'        => $provider->name,
            'logo_base64' => null,
            'logo_mime'   => null,
            'copyright'   => '© ' . date('Y') . ' ' . $provider->name,
            'contact'     => trim(($provider->address ?? '') . ' · ' . ($provider->contact_phone ?? '')),
        ];

        if ($provider->hasFeature('custom_logo') && $provider->logo_url) {
            $logoPath = storage_path('app/public/' . $provider->logo_url);
            if (file_exists($logoPath)) {
                $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                $mime = ($ext === 'jpg') ? 'jpeg' : $ext;
                $brand['logo_base64'] = base64_encode(file_get_contents($logoPath));
                $brand['logo_mime']   = 'image/' . $mime;
            }
        }

        return $brand;
    }

    /**
     * Build logo for email body (hybrid: <=50KB base64, else URL).
     * Plan-gated: only if 'custom_logo' feature.
     */
    public function buildLogoForEmail($provider): array
    {
        $logoData = null;
        $logoUrl  = null;

        if ($provider->hasFeature('custom_logo') && $provider->logo_url) {
            $logoPath = storage_path('app/public/' . $provider->logo_url);
            if (file_exists($logoPath)) {
                $size = filesize($logoPath);
                if ($size <= 51200) {
                    $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                    $mime = ($ext === 'jpg') ? 'jpeg' : $ext;
                    $logoData = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
                } else {
                    $logoUrl = url('storage/' . $provider->logo_url);
                }
            }
        }

        return ['logoData' => $logoData, 'logoUrl' => $logoUrl];
    }
}