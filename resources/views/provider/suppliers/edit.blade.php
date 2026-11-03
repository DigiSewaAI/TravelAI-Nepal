@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.edit_supplier'))
@section('page-title', __('messages.edit_supplier'))

@section('content')
<div style="padding:24px;max-width:720px;margin:0 auto;">
    <h1 style="font-size:22px;font-weight:800;margin:0 0 4px;color:#111827;">{{ __('messages.edit_supplier') }}</h1>
    <p style="font-size:13px;color:#6b7280;margin:0 0 20px;">{{ $supplier->name }}</p>

    <form method="POST" action="{{ route('provider.suppliers.update', $supplier) }}"
          style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
        @csrf
        @method('PUT')

        @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px;">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
        @endif

        <label style="display:block;margin-bottom:16px;">
            <span style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;">Name *</span>
            <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required maxlength="255"
                   style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:10px;font-size:13px;box-sizing:border-box;">
        </label>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <label>
                <span style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;">{{ __('messages.phone') }}</span>
                <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" maxlength="50"
                       style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:10px;font-size:13px;box-sizing:border-box;">
            </label>
            <label>
                <span style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;">{{ __('messages.email') }}</span>
                <input type="email" name="email" value="{{ old('email', $supplier->email) }}" maxlength="255"
                       style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:10px;font-size:13px;box-sizing:border-box;">
            </label>
        </div>

        <label style="display:block;margin-bottom:16px;">
            <span style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;">{{ __('messages.address') }}</span>
            <input type="text" name="address" value="{{ old('address', $supplier->address) }}" maxlength="500"
                   style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:10px;font-size:13px;box-sizing:border-box;">
        </label>

        <label style="display:block;margin-bottom:16px;">
            <span style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;">{{ __('messages.notes') }}</span>
            <textarea name="notes" rows="3" maxlength="2000"
                      style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:10px;font-size:13px;box-sizing:border-box;font-family:inherit;">{{ old('notes', $supplier->notes) }}</textarea>
        </label>

        <label style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;color:#374151;">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ $supplier->is_active ? 'checked' : '' }}>
            Active
        </label>

        <div style="display:flex;gap:10px;">
            <button type="submit"
                    style="padding:10px 20px;background:var(--accent,#6366f1);color:#fff;border:0;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;">
                Update Supplier
            </button>
            <a href="{{ route('provider.suppliers.index') }}"
               style="padding:10px 20px;background:#f3f4f6;color:#374151;border-radius:10px;text-decoration:none;font-size:13px;font-weight:700;">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection