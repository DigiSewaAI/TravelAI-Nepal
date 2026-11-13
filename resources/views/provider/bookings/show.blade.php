@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.booking_detail_title', ['id' => $booking->booking_number ?? $booking->id]))
@section('header', __('messages.booking_detail_header'))
@section('page-title', __('messages.booking_detail_header'))
@section('page-subtitle', $booking->created_at->format('M d, Y H:i'))

@section('content')

@php
    $cv          = 'var(--accent, #2563eb)';
    $cvSoft      = 'var(--accent-soft, #eff6ff)';
    $cvCard      = 'var(--card, #ffffff)';
    $cvLine      = 'var(--line, #e5e7eb)';
    $cvText      = 'var(--text, #1f2937)';
    $cvMuted     = 'var(--muted, #6b7280)';
    $cvR         = 'var(--radius, 12px)';
    $cvGreen     = 'var(--green, #10b981)';
    $cvGreenSoft = 'var(--green-soft, #d1fae5)';
    $cvAmber     = 'var(--amber, #f59e0b)';
    $cvAmberSoft = 'var(--amber-soft, #fef3c7)';
    $cvRed       = 'var(--red, #ef4444)';
    $cvRedSoft   = 'var(--red-soft, #fee2e2)';

    $statusMap = [
        'pending'   => ['label' => __('messages.pending'),   'bg' => $cvAmberSoft, 'fg' => $cvAmber],
        'confirmed' => ['label' => __('messages.confirmed'), 'bg' => $cvSoft,      'fg' => $cv],
        'completed' => ['label' => __('messages.completed'), 'bg' => $cvGreenSoft, 'fg' => $cvGreen],
        'cancelled' => ['label' => __('messages.cancelled'), 'bg' => $cvRedSoft,   'fg' => $cvRed],
    ];
    $status = $statusMap[$booking->status] ?? ['label' => ucfirst($booking->status), 'bg' => $cvLine, 'fg' => $cvMuted];
@endphp

<div style="padding: 24px 20px; max-width: 960px; margin: 0 auto;">

    <div style="margin-bottom: 20px;">
        <a href="{{ route('provider.bookings.index') }}" style="display:inline-flex;align-items:center;gap:6px;color:{{ $cv }};font-size:13px;font-weight:700;text-decoration:none;">
            <i class="fas fa-arrow-left"></i> {{ __('messages.back_to_bookings') }}
        </a>
    </div>

    @if(session('success'))
        <div style="background:{{ $cvGreenSoft }};color:{{ $cvGreen }};border-left:4px solid {{ $cvGreen }};border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="fas fa-check-circle" style="margin-right:6px;"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:{{ $cvRedSoft }};color:{{ $cvRed }};border-left:4px solid {{ $cvRed }};border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>{{ session('error') }}
        </div>
    @endif

    <div style="background:{{ $cvCard }};border:1px solid {{ $cvLine }};border-radius:{{ $cvR }};padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
            <div>
                <h1 style="font-size:24px;font-weight:800;color:{{ $cvText }};margin:0 0 4px;line-height:1.2;">
                    {{ $booking->booking_number ?? '#'.$booking->id }}
                </h1>
                <p style="font-size:12px;color:{{ $cvMuted }};margin:0;">
                    {{ __('messages.booking_detail_header') }} · {{ $booking->created_at->format('M d, Y H:i') }}
                </p>
            </div>
            <span style="display:inline-block;padding:6px 16px;border-radius:20px;font-size:12px;font-weight:800;background:{{ $status['bg'] }};color:{{ $status['fg'] }};text-transform:uppercase;letter-spacing:0.5px;">
                {{ $status['label'] }}
            </span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;border-top:1px solid {{ $cvLine }};padding-top:20px;">
            <div>
                <p style="font-size:11px;color:{{ $cvMuted }};text-transform:uppercase;letter-spacing:0.6px;font-weight:700;margin:0 0 4px;">{{ __('messages.traveler') }}</p>
                <p style="font-size:14px;font-weight:700;color:{{ $cvText }};margin:0 0 2px;">{{ $booking->traveler->name ?? __('messages.guest') }}</p>
                <p style="font-size:12px;color:{{ $cvMuted }};margin:0;">{{ $booking->traveler->email ?? __('messages.na') }}</p>
            </div>
            <div>
                <p style="font-size:11px;color:{{ $cvMuted }};text-transform:uppercase;letter-spacing:0.6px;font-weight:700;margin:0 0 4px;">{{ __('messages.service') }}</p>
                <p style="font-size:14px;font-weight:700;color:{{ $cvText }};margin:0 0 2px;">{{ $booking->service->name ?? __('messages.na') }}</p>
                <p style="font-size:12px;color:{{ $cvMuted }};margin:0;">{{ $booking->service->provider->name ?? __('messages.na') }}</p>
            </div>
            <div>
                <p style="font-size:11px;color:{{ $cvMuted }};text-transform:uppercase;letter-spacing:0.6px;font-weight:700;margin:0 0 4px;">{{ __('messages.start_date') }}</p>
                <p style="font-size:14px;font-weight:700;color:{{ $cvText }};margin:0;">{{ $booking->start_date ? $booking->start_date->format('M d, Y') : __('messages.na') }}</p>
            </div>
            <div>
                <p style="font-size:11px;color:{{ $cvMuted }};text-transform:uppercase;letter-spacing:0.6px;font-weight:700;margin:0 0 4px;">{{ __('messages.booking_date') }}</p>
                <p style="font-size:14px;font-weight:700;color:{{ $cvText }};margin:0;">{{ $booking->booking_date ? $booking->booking_date->format('M d, Y H:i') : __('messages.na') }}</p>
            </div>
        </div>
    </div>

    <div style="background:{{ $cvCard }};border:1px solid {{ $cvLine }};border-radius:{{ $cvR }};padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="font-size:14px;font-weight:800;color:{{ $cvText }};margin:0 0 12px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-qrcode" style="color:{{ $cv }};"></i> {{ __('messages.qr_code') }}
        </h3>
        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
            <div style="background:#fff;padding:12px;border:1px solid {{ $cvLine }};border-radius:10px;">
                <img src="{{ route('booking.qr', $booking) }}" alt="{{ __('messages.qr_code_alt') }}" style="width:128px;height:128px;display:block;">
            </div>
            <p style="font-size:12px;color:{{ $cvMuted }};margin:0;line-height:1.5;max-width:320px;">
                {{ __('messages.qr_code_alt') }}
            </p>
        </div>
    </div>

    <div style="background:{{ $cvCard }};border:1px solid {{ $cvLine }};border-radius:{{ $cvR }};padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="font-size:14px;font-weight:800;color:{{ $cvText }};margin:0 0 12px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-sync-alt" style="color:{{ $cv }};"></i> {{ __('messages.update_status') }}
        </h3>
        <form method="POST" action="{{ route('provider.bookings.updateStatus', $booking) }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            @csrf
            @method('PATCH')
            <select name="status" style="padding:10px 14px;border:1px solid {{ $cvLine }};border-radius:10px;font-family:inherit;font-size:13px;color:{{ $cvText }};background:#fff;min-width:180px;outline:none;cursor:pointer;">
                <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>{{ __('messages.confirmed') }}</option>
                <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>{{ __('messages.cancelled') }}</option>
            </select>
            <button type="submit" style="padding:10px 22px;background:{{ $cv }};color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.08);" onmouseover="this.style.filter='brightness(0.92)'" onmouseout="this.style.filter='none'">
                {{ __('messages.update_status') }}
            </button>
        </form>
    </div>

    <div style="background:{{ $cvCard }};border:1px solid {{ $cvLine }};border-radius:{{ $cvR }};padding:24px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="font-size:14px;font-weight:800;color:{{ $cvText }};margin:0 0 16px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-file-invoice" style="color:{{ $cv }};"></i> {{ __('messages.invoice') ?? 'Invoice' }}
        </h3>
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <a href="{{ route('provider.bookings.invoice', $booking) }}"
               style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:{{ $cvGreen }};color:#fff;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 2px 6px rgba(0,0,0,0.08);"
               onmouseover="this.style.filter='brightness(0.92)'" onmouseout="this.style.filter='none'">
                <i class="fas fa-download"></i> {{ __('messages.download_invoice') }}
            </a>

            <form method="POST" action="{{ route('provider.bookings.emailInvoice', $booking) }}"
                  onsubmit="return confirm('{{ __('messages.email_invoice_confirm') }}');"
                  style="margin:0;display:inline-block;">
                @csrf
                <button type="submit"
                        style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:{{ $cv }};color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.08);"
                        onmouseover="this.style.filter='brightness(0.92)'" onmouseout="this.style.filter='none'">
                    <i class="fas fa-envelope"></i> {{ __('messages.email_invoice_to_traveler') }}
                </button>
            </form>
        </div>
    </div>

</div>
@endsection