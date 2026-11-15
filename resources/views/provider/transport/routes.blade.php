@extends('layouts.provider-transport')
@section('title', 'Routes Management')
@section('page-title', 'Routes Management')
@section('page-subtitle', 'Manage the routes your fleet operates on')

@push('transport-head')
<style>
  .rt-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
  .rt-card { background: var(--card); border: 1px solid var(--line); border-radius: 16px; padding: 20px; transition: .2s; }
  .rt-card:hover { box-shadow: 0 8px 24px rgba(12,30,51,.08); transform: translateY(-2px); }
  .rt-route { font-size: 17px; font-weight: 800; color: var(--text); margin-bottom: 12px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
  .rt-meta { display: flex; gap: 16px; font-size: 13px; color: var(--muted); margin-bottom: 14px; flex-wrap: wrap; font-weight: 600; }
  .rt-actions { display: flex; gap: 8px; padding-top: 14px; border-top: 1px solid var(--line); }
  .rt-badge { font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; display: inline-block; }
  .rt-badge.active { background: var(--green-soft); color: #047857; }
  .rt-badge.inactive { background: var(--line); color: var(--muted); }
  .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(10,28,51,.65); z-index: 9999; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(3px); }
  .modal-box { background: #fff; border-radius: 18px; width: 100%; max-width: 500px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,.2); max-height: 90vh; overflow-y: auto; }
  .modal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
  .modal-head h3 { font-size: 18px; font-weight: 800; margin: 0; }
  .field { margin-bottom: 14px; }
  .field label { font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; display: block; margin-bottom: 4px; letter-spacing:.3px; }
  .field input, .field select { width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 10px; font-family: inherit; font-size: 13px; background:#fff; }
  .field input:focus, .field select:focus { outline: none; border-color: var(--sky); box-shadow: 0 0 0 3px var(--sky-soft); }
  .field .hint { font-size: 11px; color: var(--muted); margin-top: 4px; }
  .btn { padding: 10px 20px; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer; border: none; font-family: inherit; display: inline-flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; }
  .btn-primary { background: linear-gradient(135deg, var(--sky), var(--sky-d)); color: #fff; }
  .btn-primary:hover { filter: brightness(1.08); }
  .btn-secondary { background: var(--card); color: var(--text); border: 1px solid var(--line); }
  .btn-danger { background: var(--red-soft); color: #b91c1c; }
  .empty-state { text-align: center; padding: 60px 20px; background: var(--card); border: 2px dashed var(--line); border-radius: 16px; }
  .empty-state h3 { font-size: 16px; margin-bottom: 8px; color: var(--text); }
  .empty-state p { font-size: 13px; color: var(--muted); margin-bottom: 20px; max-width: 400px; margin-left:auto; margin-right:auto; }
  .tab-nav { display: flex; gap: 4px; margin-bottom: 24px; border-bottom: 1px solid var(--line); }
  .tab-nav a { padding: 12px 22px; font-weight: 800; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
  .tab-nav a.active { color: var(--sky-d); border-bottom: 2px solid var(--sky); }
  .tab-nav a:not(.active) { color: var(--muted); border-bottom: 2px solid transparent; }
</style>
@endpush

@section('content')

<div class="tab-nav">
    <a href="{{ route('provider.transport.routes') }}" class="{{ request()->routeIs('provider.transport.routes') ? 'active' : '' }}">🛣️ Routes</a>
    <a href="{{ route('provider.transport.trips') }}" class="{{ request()->routeIs('provider.transport.trips*') ? 'active' : '' }}">📅 Daily Departures</a>
</div>

@if(session('success'))
<div style="background:var(--green-soft);color:#047857;padding:14px 18px;border-radius:12px;margin-bottom:18px;font-size:13px;font-weight:700;border:1px solid #86efac">✅ {{ session('success') }}</div>
@endif
@if($errors->any())
<div style="background:var(--red-soft);color:#b91c1c;padding:14px 18px;border-radius:12px;margin-bottom:18px;font-size:13px;font-weight:700;border:1px solid #fca5a5">
    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
</div>
@endif

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
        <h2 style="font-size:20px;font-weight:800;margin:0 0 4px">🛣️ Your Routes</h2>
        <p style="font-size:13px;color:var(--muted);margin:0">Each route connects a departure city to a destination. You'll schedule daily trips on these routes next.</p>
    </div>
    <button onclick="openAddRouteModal()" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
        Add Route
    </button>
</div>

@if($routes->isEmpty())
<div class="empty-state">
    <div style="font-size:56px;margin-bottom:12px">🛣️</div>
    <h3>No routes yet</h3>
    <p>Create your first route to start accepting passenger bookings. E.g. Kathmandu → Pokhara.</p>
    <button onclick="openAddRouteModal()" class="btn btn-primary">Create Your First Route</button>
</div>
@else
<div class="rt-grid">
    @foreach($routes as $route)
    <div class="rt-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:12px;flex-wrap:wrap">
            <div class="rt-route">
                <span style="color:var(--sky-d)">{{ $route->fromLocation->city ?? '?' }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:var(--muted)"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                <span style="color:var(--green)">{{ $route->toLocation->city ?? '?' }}</span>
            </div>
            <span class="rt-badge {{ $route->is_active ? 'active' : 'inactive' }}">
                {{ $route->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div class="rt-meta">
            @if($route->duration_minutes > 0)
            <span>⏱ {{ intdiv($route->duration_minutes, 60) }}h {{ $route->duration_minutes % 60 }}m</span>
            @endif
            @if($route->distance_km)
            <span>📏 {{ number_format($route->distance_km, 0) }} km</span>
            @endif
            <span>📅 {{ $route->trips_count }} {{ $route->trips_count == 1 ? 'trip' : 'trips' }}</span>
        </div>
        <div class="rt-actions">
            <button type="button" onclick="openEditRouteModal({{ $route->id }})" class="btn btn-secondary" style="flex:1;padding:8px;font-size:12px">
                ✏️ Edit
            </button>
            <form action="{{ route('provider.transport.routes.destroy', $route) }}" method="POST" onsubmit="return confirm('Delete this route?');" style="margin:0">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger" style="padding:8px 12px;font-size:12px">🗑</button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Add/Edit Modal --}}
<div id="routeModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="routeModalTitle">Add Route</h3>
            <button type="button" onclick="closeRouteModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form id="routeForm" method="POST">
            @csrf
            <span id="routeMethodField"></span>

            <div class="field">
                <label>From City</label>
                <select name="from_location_id" id="rf_from" required>
                    <option value="">— Select City —</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">{{ $loc->city }}@if($loc->state) — {{ $loc->state }}@endif</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label>To City</label>
                <select name="to_location_id" id="rf_to" required>
                    <option value="">— Select City —</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">{{ $loc->city }}@if($loc->state) — {{ $loc->state }}@endif</option>
                    @endforeach
                </select>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="field">
                    <label>Duration (minutes)</label>
                    <input type="number" name="duration_minutes" id="rf_duration" min="0" placeholder="e.g. 360">
                    <div class="hint">360 = 6 hours</div>
                </div>
                <div class="field">
                    <label>Distance (km)</label>
                    <input type="number" name="distance_km" id="rf_distance" min="0" step="0.01" placeholder="e.g. 200">
                </div>
            </div>

            <div class="field" id="routeActiveField" style="display:none">
                <label style="display:flex;align-items:center;gap:8px;text-transform:none;font-size:13px;font-weight:700;cursor:pointer">
                    <input type="checkbox" name="is_active" value="1" id="rf_active" style="width:auto;margin:0">
                    Active (accepting bookings)
                </label>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="button" onclick="closeRouteModal()" class="btn btn-secondary" style="flex:1">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1" id="routeSubmitBtn">Create Route</button>
            </div>
        </form>
    </div>
</div>

<script>
const ALL_ROUTES = @json($routes);

function openAddRouteModal() {
    document.getElementById('routeModalTitle').textContent = 'Add Route';
    document.getElementById('routeForm').action = '{{ route('provider.transport.routes.store') }}';
    document.getElementById('routeMethodField').innerHTML = '';
    document.getElementById('routeSubmitBtn').textContent = 'Create Route';
    document.getElementById('rf_from').value = '';
    document.getElementById('rf_to').value = '';
    document.getElementById('rf_duration').value = '';
    document.getElementById('rf_distance').value = '';
    document.getElementById('rf_active').checked = true;
    document.getElementById('routeActiveField').style.display = 'none';
    document.getElementById('routeModal').style.display = 'flex';
}

function openEditRouteModal(id) {
    const route = ALL_ROUTES.find(r => r.id === id);
    if (!route) return;

    document.getElementById('routeModalTitle').textContent = 'Edit Route';
    document.getElementById('routeForm').action = '/provider/transport/routes/' + id;
    document.getElementById('routeMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('routeSubmitBtn').textContent = 'Save Changes';
    document.getElementById('rf_from').value = route.from_location_id;
    document.getElementById('rf_to').value = route.to_location_id;
    document.getElementById('rf_duration').value = route.duration_minutes || '';
    document.getElementById('rf_distance').value = route.distance_km || '';
    document.getElementById('rf_active').checked = !!route.is_active;
    document.getElementById('routeActiveField').style.display = 'block';
    document.getElementById('routeModal').style.display = 'flex';
}

function closeRouteModal() {
    document.getElementById('routeModal').style.display = 'none';
}

document.getElementById('routeModal').addEventListener('click', function(e) {
    if (e.target === this) closeRouteModal();
});
</script>

@endsection