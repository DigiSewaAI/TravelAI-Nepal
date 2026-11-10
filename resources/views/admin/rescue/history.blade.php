@extends('layouts.admin')

@section('title', 'Dispatch History')
@section('page-title', 'Dispatch History')

@section('content')

<style>
    .dh-wrap { max-width: 1400px; margin: 0 auto; }
    .dh-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .dh-header h2 { font-size: 24px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em; }
    .dh-header p { font-size: 13px; color: #64748b; margin: 4px 0 0; }
    
    .dh-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(15,23,42,.06); overflow: hidden; }
    .dh-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .dh-table th { text-align: left; padding: 14px 16px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; font-weight: 800; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
    .dh-table td { padding: 14px 16px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
    .dh-table tbody tr:last-child td { border-bottom: none; }
    .dh-table tbody tr:hover { background: #f8fafc; }
    
    .dh-badge { display: inline-flex; padding: 3px 9px; border-radius: 99px; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; }
    .bg-completed { background: #d1fae5; color: #047857; }
    .bg-cancelled { background: #fee2e2; color: #991b1b; }
    
    .dh-empty { padding: 60px 20px; text-align: center; color: #94a3b8; font-size: 14px; }
</style>

<div class="dh-wrap">
    <div class="dh-header">
        <div>
            <h2>Rescue Dispatch History</h2>
            <p>Complete log of all completed and cancelled rescue operations.</p>
        </div>
    </div>

    <div class="dh-card">
        <div style="overflow-x: auto;">
            <table class="dh-table">
                <thead>
                    <tr>
                        <th>Mission ID</th>
                        <th>Traveler</th>
                        <th>Rescue Provider</th>
                        <th>Completed/Cancelled At</th>
                        <th>Final Cost</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($missions as $mission)
                        <tr>
                            <td style="font-family: monospace; font-weight: 800; color: #991b1b;">
                                {{ $mission->mission_number }}
                            </td>
                            <td style="font-weight: 600;">
                                {{ $mission->sos->traveler->name ?? 'Unknown' }}
                            </td>
                            <td>
                                {{ $mission->provider->name ?? 'Unknown Provider' }}
                            </td>
                            <td style="color: #64748b; font-size: 12px;">
                                {{ $mission->updated_at->format('M d, Y - H:i') }}
                            </td>
                            <td style="font-weight: 700;">
                                {{ $mission->final_cost ? 'NPR ' . number_format($mission->final_cost, 2) : '—' }}
                            </td>
                            <td>
                                <span class="dh-badge {{ $mission->status === 'completed' ? 'bg-completed' : 'bg-cancelled' }}">
                                    {{ $mission->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="dh-empty">
                                No completed or cancelled missions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div style="margin-top: 20px;">
        {{ $missions->links() }}
    </div>
</div>

@endsection