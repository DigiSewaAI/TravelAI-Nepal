@extends('layouts.provider-rescue')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log')
@section('page-sub', 'Immutable trail · who · what · when')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Audit Log</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Every action recorded — who, what, when. Immutable trail for compliance.</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="scroll-text"></i>
            <h2>Recent Events</h2>
            <span class="pill pill-gray">{{ $auditLogs->total() }} entries</span>
        </div>
    </div>
    <div>
        @forelse($auditLogs as $log)
            <div style="display:flex;gap:10px;padding:16px;border-bottom:1px solid var(--border)">
                <span style="font-size:11px;color:var(--text-light);font-family:'JetBrains Mono',monospace;font-weight:600;min-width:48px;padding-top:2px">
                    {{ $log->created_at ? $log->created_at->format('H:i') : '—' }}
                </span>
                <span style="width:7px;height:7px;border-radius:50%;margin-top:6px;background:var(--info);flex-shrink:0"></span>
                <div style="flex:1;font-size:12.5px;color:var(--text-muted);line-height:1.45">
                    <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px;background:var(--surface-3);color:var(--text-muted);border:1px solid var(--border);margin-right:6px;text-transform:uppercase">{{ $log->action }}</span>
                    <span style="color:var(--text);font-weight:600">System</span> {{ $log->notes ?? 'Action logged' }}
                    @if($log->from_status && $log->to_status)
                        <span style="font-size:11px; color:var(--text-light); display:block; margin-top:4px;">
                            Status: {{ $log->from_status }} → {{ $log->to_status }}
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div style="padding:40px;text-align:center;color:var(--text-muted)">
                <i data-lucide="scroll-text" style="width:32px;height:32px;margin:0 auto 8px;opacity:.4"></i>
                <div>No audit logs recorded yet.</div>
            </div>
        @endforelse
    </div>
</div>

<div style="margin-top:20px">
    {{ $auditLogs->links() }}
</div>

@endsection

@push('rescue-scripts')
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });</script>
@endpush