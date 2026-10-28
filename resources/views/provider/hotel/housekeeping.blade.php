@extends('layouts.provider-hotel')

@section('title', __('messages.housekeeping_tasks_page'))
@section('page-title', __('messages.housekeeping_tasks_page'))
@section('page-subtitle', __('messages.housekeeping_tasks_subtitle'))

@push('hotel-head')
<style>
  .page-card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px}
  .tbl-wrap{overflow-x:auto;margin:0 -4px}
  .tbl{width:100%;border-collapse:collapse;min-width:700px}
  .tbl th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:#9aa8bc;font-weight:800;padding:0 12px 12px;border-bottom:1px solid var(--line);white-space:nowrap}
  .tbl td{padding:13px 12px;font-size:12.5px;font-weight:600;border-bottom:1px solid #f3f7fc;white-space:nowrap}
  .tbl tbody tr:last-child td{border-bottom:none}
  .tbl tbody tr:hover{background:#fbfdff}
  .task-ic{width:34px;height:34px;border-radius:10px;background:#f4f7fc;display:grid;place-items:center;font-size:16px;flex-shrink:0}
  .task-row{display:flex;align-items:center;gap:10px}
  .task-name{font-weight:800;font-size:12.5px}
  .pill{font-size:10.5px;font-weight:800;padding:4px 10px;border-radius:20px;display:inline-block}
  .p-done{background:var(--green-soft);color:#047857}
  .p-prog{background:var(--blue-soft);color:#1d4ed8}
  .p-queue{background:#f1f5f9;color:#64748b}
  .p-muted{background:#f1f5f9;color:#64748b}
  .empty{text-align:center;padding:44px 20px;font-size:13px;font-weight:600;color:var(--muted)}
  .empty div{font-size:38px;margin-bottom:10px;opacity:.5}
  .act-btn{padding:6px 10px;border-radius:7px;border:none;cursor:pointer;font-family:inherit;font-size:11px;font-weight:800;margin-right:4px;transition:.15s}
  .act-del{background:var(--red-soft);color:#b91c1c}
  .act-del:hover{background:#dc2626;color:#fff}
  .status-select{padding:5px 8px;border-radius:6px;border:1px solid var(--line);font-size:11px;font-weight:700;font-family:inherit;cursor:pointer;margin-right:4px}
  .alert-box{padding:11px 15px;border-radius:10px;font-size:12.5px;font-weight:700;margin-bottom:14px;display:flex;gap:10px;align-items:center;border-left:4px solid}
  .alert-ok{background:var(--green-soft);color:#047857;border-color:#10b981}
  .alert-err{background:var(--red-soft);color:#b91c1c;border-color:#dc2626}
</style>
@endpush

@section('content')
@if(session('success'))
  <div class="alert-box alert-ok">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert-box alert-err">⚠ {{ session('error') }}</div>
@endif
@if($errors->any())
  <div class="alert-box alert-err">⚠ {{ $errors->first() }}</div>
@endif

<div class="page-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;gap:10px;flex-wrap:wrap;">
    <div style="font-size:15px;font-weight:800;color:var(--text);">{{ __('messages.housekeeping_tasks_page') }} ({{ $tasks->count() }})</div>
    <button type="button" onclick="openAddTaskModal()" style="background:var(--accent);color:#fff;padding:8px 15px;border-radius:8px;border:none;cursor:pointer;font-family:inherit;font-size:12px;font-weight:700;">+ Add Task</button>
  </div>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>{{ __('messages.task_label') }}</th>
          <th>{{ __('messages.room') }}</th>
          <th>{{ __('messages.assigned_to') }}</th>
          <th>{{ __('messages.status') }}</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($tasks as $task)
          @php
            $statusMap = [
              'done'        => ['label' => __('messages.status_done'),        'cls' => 'p-done'],
              'in_progress' => ['label' => __('messages.status_in_progress'), 'cls' => 'p-prog'],
              'queue'       => ['label' => __('messages.status_queue'),       'cls' => 'p-queue'],
            ];
            $st = $statusMap[$task->status] ?? ['label' => ucfirst($task->status), 'cls' => 'p-muted'];
          @endphp
          <tr>
            <td>
              <div class="task-row">
                <div class="task-ic">🧹</div>
                <div class="task-name">{{ $task->task_name }}</div>
              </div>
            </td>
            <td style="font-weight:800">{{ __('messages.room') }} {{ $task->room->room_number ?? '—' }}</td>
            <td>{{ $task->assigned_to ?? __('messages.unassigned') }}</td>
            <td><span class="pill {{ $st['cls'] }}">{{ $st['label'] }}</span></td>
            <td style="text-align:right;">
              <form action="{{ route('provider.hotel.housekeeping.status', $task) }}" method="POST" style="display:inline;margin:0;">
                @csrf @method('PATCH')
                <select name="status" class="status-select" onchange="this.form.submit()">
                  <option value="queue" {{ $task->status === 'queue' ? 'selected' : '' }}>Queue</option>
                  <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                  <option value="done" {{ $task->status === 'done' ? 'selected' : '' }}>Done</option>
                </select>
              </form>
              <form action="{{ route('provider.hotel.housekeeping.destroy', $task) }}" method="POST" style="display:inline;margin:0;" onsubmit="return confirm('Delete this task?')">
                @csrf @method('DELETE')
                <button type="submit" class="act-btn act-del"><i class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5">
              <div class="empty"><div>🧹</div>{{ __('messages.no_housekeeping_tasks') }}</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Add Task Modal --}}
<div id="addTaskModal" style="display:none;position:fixed;inset:0;background:rgba(14,23,38,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
  <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:420px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.2);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">Add New Task</h3>
      <button type="button" onclick="closeAddTaskModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
    </div>
    <form action="{{ route('provider.hotel.housekeeping.store') }}" method="POST">
      @csrf
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Task Name</label>
        <input type="text" name="task_name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="Clean Room">
      </div>
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Room (Optional)</label>
        <select name="room_id" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
          <option value="">— No specific room —</option>
          @foreach($rooms as $r)
            <option value="{{ $r->id }}">Room {{ $r->room_number }} ({{ $r->room_type }})</option>
          @endforeach
        </select>
      </div>
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Assigned To (Optional)</label>
        <input type="text" name="assigned_to" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="Sita">
      </div>
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Status</label>
        <select name="status" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
          <option value="queue">Queue</option>
          <option value="in_progress">In Progress</option>
          <option value="done">Done</option>
        </select>
      </div>
      <button type="submit" class="btn-primary" style="width:100%;justify-content:center">Add Task</button>
    </form>
  </div>
</div>

@php
    if (!isset($occupiedRooms) || !isset($totalRooms)) {
        $__p = $provider ?? (auth()->check() ? auth()->user()->ownProvider() : null);
        if ($__p) {
            $totalRooms = \App\Models\HotelRoom::where('provider_id', $__p->id)->count();
            $occupiedRooms = \App\Models\HotelRoom::where('provider_id', $__p->id)->where('status', 'occupied')->count();
        } else { $totalRooms = 0; $occupiedRooms = 0; }
    }
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const occ = {{ (int) $occupiedRooms }};
    const total = {{ (int) $totalRooms }};
    const pct = total > 0 ? Math.round((occ / total) * 100) : 0;
    const el = document.getElementById('occVal');
    const bar = document.getElementById('occBar');
    if (el) el.innerHTML = pct + '% <small>' + occ + ' / ' + total + '</small>';
    if (bar) bar.style.width = pct + '%';
});

function openAddTaskModal() { document.getElementById('addTaskModal').style.display = 'flex'; }
function closeAddTaskModal() { document.getElementById('addTaskModal').style.display = 'none'; }
document.getElementById('addTaskModal').addEventListener('click', function(e) { if (e.target === this) closeAddTaskModal(); });
</script>
@endsection