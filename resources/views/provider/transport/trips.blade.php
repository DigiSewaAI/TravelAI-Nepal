@extends('layouts.provider-transport')
@section('title', 'Daily Departures')
@section('page-title', 'Routes & Trips')
@section('page-subtitle', 'Schedule departures and manage bookings')

@push('transport-head')
<style>
  .tab-nav { display: flex; gap: 4px; margin-bottom: 24px; border-bottom: 1px solid var(--line); }
  .tab-nav a { padding: 12px 22px; font-weight: 800; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
  .tab-nav a.active { color: var(--sky-d); border-bottom: 2px solid var(--sky); }
  .tab-nav a:not(.active) { color: var(--muted); border-bottom: 2px solid transparent; }
  .filter-bar { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; background: var(--card); border: 1px solid var(--line); border-radius: 14px; padding: 16px; margin-bottom: 20px; }
  .filter-field { flex: 1; min-width: 140px; }
  .filter-field label { font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; display: block; margin-bottom: 4px; letter-spacing: .3px; }
  .filter-field input, .filter-field select { width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 10px; font-family: inherit; font-size: 13px; }
  .trip-date-head { font-size: 15px; font-weight: 800; color: var(--text); margin: 24px 0 12px; padding-bottom: 8px; border-bottom: 1px dashed var(--line); }
  .trip-card { background: var(--card); border: 1px solid var(--line); border-radius: 14px; padding: 18px; margin-bottom: 12px; transition: .2s; display: flex; flex-direction: column; gap: 10px; }
  .trip-card:hover { box-shadow: 0 6px 20px rgba(12,30,51,.08); }
  .trip-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; }
  .trip-time { font-size: 22px; font-weight: 900; color: var(--sky-d); font-family: 'Courier New', monospace; letter-spacing: -1px; }
  .trip-vehicle { font-size: 14px; font-weight: 800; color: var(--text); }
  .trip-route { font-size: 13px; color: var(--muted); font-weight: 700; margin-top: 2px; }
  .trip-meta { display: flex; gap: 16px; flex-wrap: wrap; font-size: 12.5px; color: var(--muted); font-weight: 600; padding: 10px 0; border-top: 1px dashed var(--line); border-bottom: 1px dashed var(--line); }
  .trip-meta strong { color: var(--text); }
  .trip-actions { display: flex; gap: 8px; flex-wrap: wrap; }
  .badge { font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: .3px; }
  .badge.scheduled { background: var(--sky-soft); color: var(--sky-d); }
  .badge.completed { background: var(--green-soft); color: #047857; }
  .badge.cancelled { background: var(--red-soft); color: #b91c1c; }
  .btn { padding: 9px 16px; border-radius: 9px; font-weight: 800; font-size: 12px; cursor: pointer; border: none; font-family: inherit; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
  .btn-primary { background: linear-gradient(135deg, var(--sky), var(--sky-d)); color: #fff; }
  .btn-primary:hover { filter: brightness(1.08); }
  .btn-secondary { background: var(--card); color: var(--text); border: 1px solid var(--line); }
  .btn-danger { background: var(--red-soft); color: #b91c1c; }
  .empty-state { text-align: center; padding: 60px 20px; background: var(--card); border: 2px dashed var(--line); border-radius: 16px; }
  .empty-state h3 { font-size: 16px; margin-bottom: 8px; }
  .empty-state p { font-size: 13px; color: var(--muted); margin-bottom: 20px; max-width: 420px; margin-left: auto; margin-right: auto; }
  .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(10,28,51,.65); z-index: 9999; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(3px); }
  .modal-box { background: #fff; border-radius: 18px; width: 100%; max-width: 520px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,.2); max-height: 90vh; overflow-y: auto; }
  .modal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
  .modal-head h3 { font-size: 18px; font-weight: 800; margin: 0; }
  .field { margin-bottom: 14px; }
  .field label { font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; display: block; margin-bottom: 4px; }
  .field input, .field select { width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 10px; font-family: inherit; font-size: 13px; background: #fff; }
  .field input:focus, .field select:focus { outline: none; border-color: var(--sky); box-shadow: 0 0 0 3px var(--sky-soft); }
  .field .hint { font-size: 11px; color: var(--muted); margin-top: 4px; }
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
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

<form method="GET" class="filter-bar">
    <div class="filter-field">
        <label>Travel Date</label>
        <input type="date" name="date" value="{{ $filterDate }}">
    </div>
    <div class="filter-field">
        <label>Route</label>
        <select name="route_id">
            <option value="">All Routes</option>
            @foreach($routes as $r)
            <option value="{{ $r->id }}" @selected($filterRoute == $r->id)>{{ $r->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-field">
        <label>Status</label>
        <select name="status">
            <option value="scheduled" @selected($filterStatus === 'scheduled')>Scheduled</option>
            <option value="completed" @selected($filterStatus === 'completed')>Completed</option>
            <option value="cancelled" @selected($filterStatus === 'cancelled')>Cancelled</option>
            <option value="" @selected($filterStatus === '')>All</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Apply</button>
    <button type="button" onclick="openAddTripModal()" class="btn btn-primary" style="background:linear-gradient(135deg,#10b981,#059669)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
        Add Departure
    </button>
</form>

@if($trips->isEmpty())
<div class="empty-state">
    <div style="font-size:56px;margin-bottom:12px">📅</div>
    <h3>No departures for this filter</h3>
    <p>Create a scheduled departure — pick a vehicle, route, date and time. Passengers can then book seats on it.</p>
    @if($routes->isEmpty())
        <p style="color:#b45309"><strong>⚠️ No routes yet.</strong> Create a route first.</p>
        <a href="{{ route('provider.transport.routes') }}" class="btn btn-primary">Go to Routes</a>
    @else
        <button onclick="openAddTripModal()" class="btn btn-primary">Create First Departure</button>
    @endif
</div>
@else
    @foreach($trips as $date => $dateTrips)
    <div class="trip-date-head">
        📅 {{ \Carbon\Carbon::parse($date)->format('l, M d, Y') }} — {{ $dateTrips->count() }} {{ $dateTrips->count() === 1 ? 'departure' : 'departures' }}
    </div>

    @foreach($dateTrips as $trip)
    @php
        $vt = strtolower($trip->vehicle->vehicle_type ?? '');
        $icon = str_contains($vt, 'bus') ? '🚌' : (str_contains($vt, 'van') || str_contains($vt, 'hiace') ? '🚐' : (str_contains($vt, 'jeep') || str_contains($vt, 'suv') ? '🚙' : (str_contains($vt, 'bike') ? '🏍️' : '🚗')));
    @endphp
    <div class="trip-card">
        <div class="trip-head">
            <div>
                <div class="trip-time">{{ substr($trip->departure_time, 0, 5) }}</div>
                <div class="trip-vehicle">{{ $icon }} {{ $trip->vehicle->vehicle_type ?? '' }} — {{ $trip->vehicle->vehicle_number ?? '' }}</div>
                <div class="trip-route">{{ $trip->route->fromLocation->city ?? '?' }} → {{ $trip->route->toLocation->city ?? '?' }}</div>
            </div>
            <span class="badge {{ $trip->status }}">{{ $trip->status }}</span>
        </div>
        <div class="trip-meta">
            <span>💰 <strong>Rs {{ number_format($trip->price_per_seat, 0) }}</strong>/seat</span>
            @if($trip->price_per_vehicle)
            <span>🚐 <strong>Rs {{ number_format($trip->price_per_vehicle, 0) }}</strong>/vehicle</span>
            @endif
            <span>🪑 <strong>{{ $trip->seats_available }}</strong> of {{ $trip->seats_total }} seats</span>
            @if($trip->driver_name)
            <span>👤 {{ $trip->driver_name }}</span>
            @endif
            @if($trip->pickup_point)
            <span>📍 {{ $trip->pickup_point }}</span>
            @endif
        </div>
        <div class="trip-actions">
            <button type="button" onclick="openEditTripModal({{ $trip->id }})" class="btn btn-secondary">✏️ Edit</button>
            <form action="{{ route('provider.transport.trips.destroy', $trip) }}" method="POST" onsubmit="return confirm('Delete this departure?');" style="margin:0">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">🗑 Delete</button>
            </form>
        </div>
    </div>
    @endforeach
    @endforeach
@endif

{{-- Add/Edit Modal --}}
<div id="tripModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="tripModalTitle">Add Departure</h3>
            <button type="button" onclick="closeTripModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form id="tripForm" method="POST">
            @csrf
            <span id="tripMethodField"></span>

            <div class="field-row">
                <div class="field">
                    <label>Date</label>
                    <input type="date" name="departure_date" id="tf_date" required min="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="field">
                    <label>Time</label>
                    <input type="time" name="departure_time" id="tf_time" required>
                </div>
            </div>

            <div class="field">
                <label>Vehicle</label>
                <select name="vehicle_id" id="tf_vehicle" required onchange="onVehicleChange()">
                    <option value="">— Select Vehicle —</option>
                    @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" data-capacity="{{ $v->capacity }}" data-driver="{{ $v->driver_name ?? '' }}">
                        {{ $v->vehicle_number }} — {{ $v->vehicle_type }} ({{ $v->capacity }} seats)
                    </option>
                    @endforeach
                </select>
                <div class="hint" id="tf_seats_hint"></div>
            </div>

            <div class="field">
                <label>Route</label>
                <select name="route_id" id="tf_route" required>
                    <option value="">— Select Route —</option>
                    @foreach($routes as $r)
                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field-row">
                <div class="field">
                    <label>Price per Seat (Rs)</label>
                    <input type="number" name="price_per_seat" id="tf_price_seat" required min="0" step="0.01" placeholder="1500">
                </div>
                <div class="field">
                    <label>Full Vehicle (Rs, optional)</label>
                    <input type="number" name="price_per_vehicle" id="tf_price_vehicle" min="0" step="0.01" placeholder="45000">
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label>Driver Name</label>
                    <input type="text" name="driver_name" id="tf_driver" maxlength="100">
                </div>
                <div class="field">
                    <label>Pickup Point</label>
                    <input type="text" name="pickup_point" id="tf_pickup" maxlength="255" placeholder="e.g. Putalisadak Bus Park">
                </div>
            </div>

            <div class="field" id="tripStatusField" style="display:none">
                <label>Status</label>
                <select name="status" id="tf_status">
                    <option value="scheduled">Scheduled</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="button" onclick="closeTripModal()" class="btn btn-secondary" style="flex:1">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1" id="tripSubmitBtn">Create Departure</button>
            </div>
        </form>
    </div>
</div>

<script>
const ALL_TRIPS = @json($tripsForJs);

function onVehicleChange() {
    const sel = document.getElementById('tf_vehicle');
    const opt = sel.options[sel.selectedIndex];
    const cap = opt?.dataset?.capacity || 0;
    const drv = opt?.dataset?.driver || '';
    const hint = document.getElementById('tf_seats_hint');
    if (cap > 0) {
        hint.textContent = 'This vehicle has ' + cap + ' seats — passengers can book up to ' + cap + '.';
    } else {
        hint.textContent = '';
    }
    const driverInput = document.getElementById('tf_driver');
    if (driverInput && !driverInput.value && drv) driverInput.value = drv;
}

function openAddTripModal() {
    document.getElementById('tripModalTitle').textContent = 'Add Departure';
    document.getElementById('tripForm').action = '{{ route('provider.transport.trips.store') }}';
    document.getElementById('tripMethodField').innerHTML = '';
    document.getElementById('tripSubmitBtn').textContent = 'Create Departure';
    document.getElementById('tf_date').value = '{{ $filterDate }}';
    document.getElementById('tf_time').value = '';
    document.getElementById('tf_vehicle').value = '';
    document.getElementById('tf_route').value = '';
    document.getElementById('tf_price_seat').value = '';
    document.getElementById('tf_price_vehicle').value = '';
    document.getElementById('tf_driver').value = '';
    document.getElementById('tf_pickup').value = '';
    document.getElementById('tripStatusField').style.display = 'none';
    document.getElementById('tf_seats_hint').textContent = '';
    document.getElementById('tripModal').style.display = 'flex';
}

function openEditTripModal(id) {
    const t = ALL_TRIPS.find(x => x.id === id);
    if (!t) return;

    document.getElementById('tripModalTitle').textContent = 'Edit Departure';
    document.getElementById('tripForm').action = '/provider/transport/trips/' + id;
    document.getElementById('tripMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('tripSubmitBtn').textContent = 'Save Changes';
    document.getElementById('tf_date').value = t.departure_date;
    document.getElementById('tf_time').value = t.departure_time;
    document.getElementById('tf_vehicle').value = t.vehicle_id;
    document.getElementById('tf_route').value = t.route_id;
    document.getElementById('tf_price_seat').value = t.price_per_seat;
    document.getElementById('tf_price_vehicle').value = t.price_per_vehicle ?? '';
    document.getElementById('tf_driver').value = t.driver_name ?? '';
    document.getElementById('tf_pickup').value = t.pickup_point ?? '';
    document.getElementById('tf_status').value = t.status;
    document.getElementById('tripStatusField').style.display = 'block';
    onVehicleChange();
    document.getElementById('tripModal').style.display = 'flex';
}

function closeTripModal() {
    document.getElementById('tripModal').style.display = 'none';
}

document.getElementById('tripModal').addEventListener('click', function(e) {
    if (e.target === this) closeTripModal();
});
</script>

@endsection