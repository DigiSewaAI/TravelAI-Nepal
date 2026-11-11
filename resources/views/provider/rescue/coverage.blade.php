@extends('layouts.provider-rescue')

@section('title', 'Coverage Areas')
@section('page-title', 'Coverage Areas')
@section('page-sub', 'Districts and radius you can respond to')

@section('content')

@if(session('success'))
<div style="background:var(--success-light);border:1px solid var(--success);color:var(--success-dark);padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
  <i data-lucide="check-circle"></i> {{ session('success') }}
</div>
@endif

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Coverage Areas</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Select districts and set your maximum response radius</p>
    </div>
</div>

<form action="{{ route('provider.rescue.coverage.update') }}" method="POST">
    @csrf
    <div class="content-grid" style="grid-template-columns: 1fr 1fr;">
        
        {{-- Active Districts --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i data-lucide="map-pin"></i>
                    <h2>Active Districts</h2>
                </div>
                <span class="pill pill-green">Select all that apply</span>
            </div>
            <div class="card-body">
                <div style="display:flex;flex-wrap:wrap;gap:8px">
                    @php
                        $districts = ['Solukhumbu', 'Khumbu', 'Dolakha', 'Sindhupalchok', 'Rasuwa', 'Manang', 'Mustang', 'Kathmandu', 'Kaski', 'Myagdi', 'Taplejung'];
                        $activeDistricts = $rescueProvider->coverage_districts ?? [];
                    @endphp
                    
                    @foreach($districts as $district)
                        <label style="display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border-radius:99px;font-size:11.5px;font-weight:600;border:1px solid var(--border);background:var(--surface);color:var(--text-muted);cursor:pointer;transition:all .15s; {{ in_array($district, $activeDistricts) ? 'background:var(--primary-light);border-color:var(--primary);color:var(--primary-dark);' : '' }}">
                            <input type="checkbox" name="coverage_districts[]" value="{{ $district }}" {{ in_array($district, $activeDistricts) ? 'checked' : '' }} style="display:none" onchange="this.parentElement.style.background = this.checked ? 'var(--primary-light)' : 'var(--surface)'; this.parentElement.style.borderColor = this.checked ? 'var(--primary)' : 'var(--border)'; this.parentElement.style.color = this.checked ? 'var(--primary-dark)' : 'var(--text-muted)';">
                            <span style="width:6px;height:6px;border-radius:50%;background:{{ in_array($district, $activeDistricts) ? 'var(--primary)' : 'var(--border-strong)' }}"></span>
                            {{ $district }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Base & Radius --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i data-lucide="crosshair"></i>
                    <h2>Base & Radius</h2>
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:20px">
                <div>
                    <div style="font-size:11.5px;color:var(--text-muted);font-weight:600;margin-bottom:6px">Primary Base</div>
                    <div style="font-size:13.5px;font-weight:700">{{ $provider->address ?? 'Not specified' }}</div>
                    
                    {{-- 🆕 Base Coordinates (Lat/Lng) --}}
                    <div style="display:flex; gap:10px; margin-top:10px;">
                        <div style="flex:1;">
                            <label style="font-size:11px;color:var(--text-muted);">Base Latitude</label>
                            <input type="number" step="0.0000001" name="base_lat" value="{{ $rescueProvider->base_lat ?? '28.2096' }}" required 
                                   style="width:100%; border:1px solid var(--border); border-radius:6px; padding:6px; font-size:13px;">
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:11px;color:var(--text-muted);">Base Longitude</label>
                            <input type="number" step="0.0000001" name="base_lng" value="{{ $rescueProvider->base_lng ?? '83.9856' }}" required 
                                   style="width:100%; border:1px solid var(--border); border-radius:6px; padding:6px; font-size:13px;">
                        </div>
                    </div>
                    <p style="font-size:10px; color:var(--text-muted); margin-top:4px;">Default: Pokhara (28.2096, 83.9856)</p>
                </div>
                
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                        <div style="font-size:11.5px;color:var(--text-muted);font-weight:600">Coverage Radius (km)</div>
                        <div style="font-size:13px;font-weight:700" id="radiusVal">{{ $rescueProvider->coverage_radius_km ?? 50 }} km</div>
                    </div>
                    <input type="range" name="coverage_radius_km" min="10" max="500" step="5" value="{{ $rescueProvider->coverage_radius_km ?? 50 }}" 
                           style="width:100%; accent-color:var(--primary);" 
                           oninput="document.getElementById('radiusVal').innerText = this.value + ' km'">
                </div>

                <div style="padding-top:16px;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save"></i> Save Coverage
                    </button>
                </div>
            </div>
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