@extends('layouts.provider-rescue')

@section('title', 'Pricing Configuration')
@section('page-title', 'Pricing Configuration')
@section('page-sub', 'Transparent dispatch & service rates')

@section('content')

@if(session('success'))
<div style="background:var(--success-light);border:1px solid var(--success);color:var(--success-dark);padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
  <i data-lucide="check-circle"></i> {{ session('success') }}
</div>
@endif

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Pricing Configuration</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Set your transparent dispatch and service rates</p>
    </div>
</div>

<form action="{{ route('provider.rescue.pricing.update') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="wallet"></i>
                <h2>Rate Card</h2>
            </div>
            <span class="badge badge-teal">NPR</span>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Rate / Percentage</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="cell-strong">Base Dispatch Fee</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px">
                                <span style="font-size:12px;color:var(--text-muted)">NPR</span>
                                <input type="number" name="base_rate_npr" value="{{ old('base_rate_npr', $rescueProvider->base_rate_npr ?? 0) }}" required min="0" step="100" style="width:120px;padding:6px 10px;border:1px solid var(--border);border-radius:6px">
                            </div>
                        </td>
                        <td class="cell-muted">One-time per mission</td>
                    </tr>
                    <tr>
                        <td class="cell-strong">Per km · Helicopter</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px">
                                <span style="font-size:12px;color:var(--text-muted)">NPR</span>
                                <input type="number" name="per_km_rate_npr" value="{{ old('per_km_rate_npr', $rescueProvider->per_km_rate_npr ?? 0) }}" required min="0" step="10" style="width:120px;padding:6px 10px;border:1px solid var(--border);border-radius:6px">
                                <span style="font-size:12px;color:var(--text-muted)">/km</span>
                            </div>
                        </td>
                        <td class="cell-muted">Flight distance</td>
                    </tr>
                    <tr>
                        <td class="cell-strong">Per hour · Ground</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px">
                                <span style="font-size:12px;color:var(--text-muted)">NPR</span>
                                <input type="number" name="per_hour_rate_npr" value="{{ old('per_hour_rate_npr', $rescueProvider->per_hour_rate_npr ?? 0) }}" required min="0" step="100" style="width:120px;padding:6px 10px;border:1px solid var(--border);border-radius:6px">
                                <span style="font-size:12px;color:var(--text-muted)">/hr</span>
                            </div>
                        </td>
                        <td class="cell-muted">Ambulance + crew</td>
                    </tr>
                    <tr>
                        <td class="cell-strong">Night Surcharge</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px">
                                <span style="font-size:12px;color:var(--text-muted)">+</span>
                                <input type="number" name="night_surcharge_pct" value="{{ old('night_surcharge_pct', $rescueProvider->night_surcharge_pct ?? 30) }}" required min="0" max="100" style="width:80px;padding:6px 10px;border:1px solid var(--border);border-radius:6px">
                                <span style="font-size:12px;color:var(--text-muted)">%</span>
                            </div>
                        </td>
                        <td class="cell-muted">20:00 – 06:00</td>
                    </tr>
                    <tr>
                        <td class="cell-strong">High-altitude Surcharge</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px">
                                <span style="font-size:12px;color:var(--text-muted)">+</span>
                                <input type="number" name="high_altitude_surcharge_pct" value="{{ old('high_altitude_surcharge_pct', $rescueProvider->high_altitude_surcharge_pct ?? 50) }}" required min="0" max="100" style="width:80px;padding:6px 10px;border:1px solid var(--border);border-radius:6px">
                                <span style="font-size:12px;color:var(--text-muted)">%</span>
                            </div>
                        </td>
                        <td class="cell-muted">Above 4,000 m</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div style="padding:16px;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save"></i> Save Pricing
            </button>
        </div>
    </div>
</form>

@endsection

@push('rescue-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();
    });
</script>
@endpush