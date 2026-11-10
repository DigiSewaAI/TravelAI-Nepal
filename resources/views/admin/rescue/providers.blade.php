@extends('layouts.admin')

@section('title', 'Rescue Providers')
@section('page-title', 'Rescue Providers')

@section('content')

<style>
    .rp-wrap { max-width: 1400px; margin: 0 auto; }
    .rp-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .rp-header h2 { font-size: 24px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em; }
    .rp-header p { font-size: 13px; color: #64748b; margin: 4px 0 0; }
    
    .rp-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(15,23,42,.06); overflow: hidden; }
    .rp-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .rp-table th { text-align: left; padding: 14px 16px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; font-weight: 800; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
    .rp-table td { padding: 14px 16px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
    .rp-table tbody tr:last-child td { border-bottom: none; }
    .rp-table tbody tr:hover { background: #f8fafc; }
    
    .rp-badge { display: inline-flex; padding: 3px 9px; border-radius: 99px; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; }
    .bg-active { background: #d1fae5; color: #047857; }
    .bg-pending { background: #fef3c7; color: #b45309; }
    .bg-offline { background: #f1f5f9; color: #475569; }
    
    .rp-empty { padding: 60px 20px; text-align: center; color: #94a3b8; font-size: 14px; }
</style>

<div class="rp-wrap">
    <div class="rp-header">
        <div>
            <h2>Registered Rescue Providers</h2>
            <p>Manage all rescue organizations, check their verification status and fleet size.</p>
        </div>
    </div>

    <div class="rp-card">
        <div style="overflow-x: auto;">
            <table class="rp-table">
                <thead>
                    <tr>
                        <th>Provider Name</th>
                        <th>Contact Email</th>
                        <th>Fleet Size</th>
                        <th>Coverage</th>
                        <th>Verification</th>
                        <th>Duty Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($providers as $rp)
                        <tr>
                            <td style="font-weight: 700; color: #0f172a;">
                                {{ $rp->provider->name ?? 'Unknown Provider' }}
                            </td>
                            <td style="color: #64748b;">
                                {{ $rp->provider->contact_email ?? 'N/A' }}
                            </td>
                            <td>
                                {{ $rp->fleet_size }} vehicles
                            </td>
                            <td style="color: #64748b;">
                                {{ $rp->coverage_radius_km }} km radius
                            </td>
                            <td>
                                @if($rp->provider->verification_status === 'verified')
                                    <span class="rp-badge bg-active">Verified</span>
                                @else
                                    <span class="rp-badge bg-pending">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($rp->is_available)
                                    <span class="rp-badge bg-active">Online</span>
                                @else
                                    <span class="rp-badge bg-offline">Offline</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.providers.show', $rp->provider_id) }}" style="color: #0d9488; font-weight: 700; text-decoration: none;">View Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="rp-empty">
                                No rescue providers registered yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection