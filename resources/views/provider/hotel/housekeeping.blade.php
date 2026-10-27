@extends('layouts.provider-hotel')

@section('title', __('messages.housekeeping_tasks_page'))
@section('page-title', __('messages.housekeeping_tasks_page'))
@section('page-subtitle', __('messages.housekeeping_tasks_subtitle'))

@push('hotel-head')
<style>
  .page-card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px}
  .tbl-wrap{overflow-x:auto;margin:0 -4px}
  .tbl{width:100%;border-collapse:collapse;min-width:600px}
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
</style>
@endpush

@section('content')
<div class="page-card">
  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>{{ __('messages.task_label') }}</th>
          <th>{{ __('messages.room') }}</th>
          <th>{{ __('messages.assigned_to') }}</th>
          <th>{{ __('messages.status') }}</th>
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
          </tr>
        @empty
          <tr>
            <td colspan="4">
              <div class="empty"><div>🧹</div>{{ __('messages.no_housekeeping_tasks') }}</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection