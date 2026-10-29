@extends('layouts.provider-individual')
@section('title', __('messages.my_bookings'))
@section('page-title', __('messages.my_bookings'))
@section('page-subtitle', __('messages.manage_assignments_subtitle'))

@push('ind-head')
<style>
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;}
  .tbl-wrap{overflow-x:auto;margin-top:10px}
  .tbl{width:100%;border-collapse:collapse;}
  .tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;font-weight:800;padding:0 15px 15px;border-bottom:1px solid var(--line);}
  .tbl td{padding:15px;font-size:13px;font-weight:600;border-bottom:1px solid #f3f5fb;vertical-align:middle;}
  .tbl tbody tr:hover{background:#fafbff}
  .pill{font-size:11px;font-weight:800;padding:5px 10px;border-radius:20px;display:inline-block;}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div><h3 style="font-size:16px; font-weight:800;">{{ __('messages.my_bookings') }}</h3></div>
        <div style="display:flex; gap:8px;">
            <button type="button" onclick="openAssignmentModal()" style="background:var(--emerald); color:#fff; padding:8px 15px; border-radius:8px; border:none; cursor:pointer; font-family:inherit; font-size:12px; font-weight:700;">+ {{ __('messages.add_assignment') }}</button>
            <a href="{{ route('provider.individual.dashboard') }}" class="btn-primary" style="background:var(--card); color:var(--text); border:1px solid var(--line); padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
        </div>
    </div>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.assignment_title') }}</th>
                    <th>{{ __('messages.client_name') }}</th>
                    <th>{{ __('messages.service_type') }}</th>
                    <th>{{ __('messages.start_date') }}</th>
                    <th>{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th style="text-align:right;">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                <tr>
                    <td><strong>{{ $b->title }}</strong></td>
                    <td>{{ $b->client_name }}</td>
                    <td><span class="pill" style="background:var(--emerald-soft); color:var(--emerald-d);">{{ __('messages.' . $b->service_type) ?? ucfirst($b->service_type) }}</span></td>
                    <td>{{ $b->start_date->format('M d, Y') }}</td>
                    <td style="font-weight:800">Rs {{ number_format($b->amount) }}</td>
                    <td><span class="pill" style="background:var(--amber-soft); color:#b45309;">{{ __('messages.' . $b->status) ?? ucfirst($b->status) }}</span></td>
                    <td style="text-align:right;white-space:nowrap;">
                        <button type="button" onclick="openEditAssignmentModal({{ $b->id }}, '{{ addslashes($b->title) }}', '{{ addslashes($b->client_name) }}', '{{ $b->service_type }}', '{{ $b->start_date->format("Y-m-d") }}', '{{ addslashes($b->duration) }}', {{ (float) $b->amount }}, '{{ $b->status }}')" style="padding:6px 10px;border-radius:7px;border:none;background:var(--blue-soft);color:#1d4ed8;font-size:11px;font-weight:800;cursor:pointer;font-family:inherit;margin-right:4px;"><i class="fas fa-edit"></i> {{ __('messages.edit') }}</button>
                        <form action="{{ route('provider.individual.assignments.destroy', $b) }}" method="POST" style="display:inline;margin:0;" onsubmit="return confirm('{{ __('messages.delete_assignment_confirm') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" style="padding:6px 10px;border-radius:7px;border:none;background:var(--red-soft);color:var(--red);font-size:11px;font-weight:800;cursor:pointer;font-family:inherit;"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_bookings_yet') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Assignment Modal --}}
<div id="assignmentModal" style="display:none;position:fixed;inset:0;background:rgba(15,36,25,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">{{ __('messages.new_assignment') }}</h3>
            <button type="button" onclick="closeAssignmentModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.individual.assignments.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.assignment_title') }}</label>
                    <input type="text" name="title" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.client_name') }}</label>
                    <input type="text" name="client_name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.service_type') }}</label>
                    <select name="service_type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="guide">{{ __('messages.guide') }}</option>
                        <option value="porter">{{ __('messages.porter') }}</option>
                        <option value="photographer">{{ __('messages.photographer') }}</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.start_date') }}</label>
                    <input type="date" name="start_date" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.duration') }}</label>
                    <input type="text" name="duration" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="10 days">
                </div>
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.amount') }} (Rs)</label>
                    <input type="number" name="amount" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--emerald,#10b981),var(--emerald-d,#059669));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">{{ __('messages.create_assignment') }}</button>
        </form>
    </div>
</div>

{{-- Edit Assignment Modal --}}
<div id="editAssignmentModal" style="display:none;position:fixed;inset:0;background:rgba(15,36,25,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">{{ __('messages.edit_assignment') }}</h3>
            <button type="button" onclick="closeEditAssignmentModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form id="editAssignmentForm" method="POST">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.assignment_title') }}</label>
                    <input type="text" name="title" id="ea_title" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.client_name') }}</label>
                    <input type="text" name="client_name" id="ea_client" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.service_type') }}</label>
                    <select name="service_type" id="ea_type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="guide">{{ __('messages.guide') }}</option>
                        <option value="porter">{{ __('messages.porter') }}</option>
                        <option value="photographer">{{ __('messages.photographer') }}</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.start_date') }}</label>
                    <input type="date" name="start_date" id="ea_start" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.duration') }}</label>
                    <input type="text" name="duration" id="ea_duration" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.amount') }} (Rs)</label>
                    <input type="number" name="amount" id="ea_amount" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.status') }}</label>
                    <select name="status" id="ea_status" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="pending">{{ __('messages.pending') }}</option>
                        <option value="confirmed">{{ __('messages.confirmed') }}</option>
                        <option value="completed">{{ __('messages.completed') }}</option>
                    </select>
                </div>
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--emerald,#10b981),var(--emerald-d,#059669));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">{{ __('messages.update_assignment') }}</button>
        </form>
    </div>
</div>

<script>
function openAssignmentModal() { document.getElementById('assignmentModal').style.display = 'flex'; }
function closeAssignmentModal() { document.getElementById('assignmentModal').style.display = 'none'; }
document.getElementById('assignmentModal').addEventListener('click', function(e) { if (e.target === this) closeAssignmentModal(); });

function openEditAssignmentModal(id, title, client, type, start, duration, amount, status) {
    document.getElementById('editAssignmentForm').action = '/provider/individual/assignments/' + id;
    document.getElementById('ea_title').value = title;
    document.getElementById('ea_client').value = client;
    document.getElementById('ea_type').value = type;
    document.getElementById('ea_start').value = start;
    document.getElementById('ea_duration').value = duration;
    document.getElementById('ea_amount').value = amount;
    document.getElementById('ea_status').value = status;
    document.getElementById('editAssignmentModal').style.display = 'flex';
}
function closeEditAssignmentModal() { document.getElementById('editAssignmentModal').style.display = 'none'; }
document.getElementById('editAssignmentModal').addEventListener('click', function(e) { if (e.target === this) closeEditAssignmentModal(); });
</script>
@endsection