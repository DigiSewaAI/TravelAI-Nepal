@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.suppliers'))
@section('page-title', __('messages.suppliers'))

@section('content')
<div style="padding:24px;max-width:1100px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <div>
            <h1 style="font-size:22px;font-weight:800;margin:0;color:#111827;">{{ __('messages.suppliers') }}</h1>
            <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">Manage your suppliers (stock, materials, vendors).</p>
        </div>
        <a href="{{ route('provider.suppliers.create') }}"
           style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:var(--accent,#6366f1);color:#fff;border-radius:10px;text-decoration:none;font-size:13px;font-weight:700;">
            <i class="fas fa-plus"></i> {{ __('messages.add_supplier') }}
        </a>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px;">
            {{ session('error') }}
        </div>
    @endif

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
        @if($suppliers->count() > 0)
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                        <th style="text-align:left;padding:12px 16px;font-weight:700;color:#374151;">{{ __('messages.name') }}</th>
                        <th style="text-align:left;padding:12px 16px;font-weight:700;color:#374151;">{{ __('messages.phone') }}</th>
                        <th style="text-align:left;padding:12px 16px;font-weight:700;color:#374151;">{{ __('messages.email') }}</th>
                        <th style="text-align:left;padding:12px 16px;font-weight:700;color:#374151;">{{ __('messages.status') }}</th>
                        <th style="text-align:right;padding:12px 16px;font-weight:700;color:#374151;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $s)
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:12px 16px;font-weight:600;color:#111827;">{{ $s->name }}</td>
                        <td style="padding:12px 16px;color:#374151;">{{ $s->phone ?? '—' }}</td>
                        <td style="padding:12px 16px;color:#374151;">{{ $s->email ?? '—' }}</td>
                        <td style="padding:12px 16px;">
                            <span style="padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;
                                {{ $s->is_active ? 'background:#ecfdf5;color:#065f46;' : 'background:#f3f4f6;color:#6b7280;' }}">
                                {{ $s->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:right;">
                            <a href="{{ route('provider.suppliers.edit', $s) }}" style="color:#2563eb;text-decoration:none;font-size:12px;font-weight:600;margin-right:12px;">{{ __('messages.edit') }}</a>
                            <form method="POST" action="{{ route('provider.suppliers.destroy', $s) }}" style="display:inline;" onsubmit="return confirm('Remove this supplier?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:none;border:0;color:#dc2626;font-size:12px;font-weight:600;cursor:pointer;padding:0;">{{ __('messages.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding:16px;">{{ $suppliers->links() }}</div>
        @else
            <div style="padding:48px 24px;text-align:center;">
                <i class="fas fa-truck" style="font-size:32px;color:#d1d5db;margin-bottom:12px;"></i>
                <p style="font-size:13px;color:#6b7280;margin:0;">{{ __('messages.no_suppliers_yet') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection