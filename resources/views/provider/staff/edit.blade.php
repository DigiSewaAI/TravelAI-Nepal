@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.edit_staff_title'))
@section('header', __('messages.edit_staff_header'))

@section('content')
<div class="max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">{{ __('messages.edit_staff_title') }}</h2>

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded">
            {{ session('error') }}
            @if(str_contains(session('error'), 'staff limit') || str_contains(session('error'), 'Staff limit'))
                <br>
                <a href="{{ route('provider.subscriptions.index') }}" class="text-blue-600 hover:text-blue-800 font-medium underline inline-block mt-1">
                    <i class="fas fa-arrow-up"></i> {{ __('messages.upgrade_plan') }}
                </a>
            @endif
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('provider.staff.update', $staff) }}" class="bg-white p-6 rounded-lg shadow">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block font-medium mb-1">{{ __('messages.full_name') }} *</label>
            <input type="text" name="name" class="w-full border rounded-lg px-4 py-2" value="{{ old('name', $staff->user->name) }}" required>
            @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">{{ __('messages.email') }}</label>
            <p class="text-gray-600">{{ $staff->user->email }}</p>
            <p class="text-xs text-gray-400">{{ __('messages.email_cannot_be_changed') }}</p>
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">{{ __('messages.role_optional') }}</label>
            <input type="text" name="role" class="w-full border rounded-lg px-4 py-2" value="{{ old('role', $staff->role) }}" placeholder="{{ __('messages.role_placeholder') }}">
            @error('role') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- STAFF-PERMISSIONS-02: Access Control -->
        <div class="mb-6 border-t pt-4">
            <label class="block font-bold text-lg mb-2 text-gray-800">{{ __('messages.staff_access_permissions') }}</label>
            <p class="text-sm text-gray-500 mb-4">{{ __('messages.select_modules') }}</p>
            
            @php
                $currentPermissions = $staff->permissions ?? [];
                $availablePermissions = [
                    'view_bookings'   => __('messages.view_bookings'),
                    'manage_checkins' => __('messages.manage_checkins'),
                    'view_payments'   => __('messages.view_payments'),
                ];
                $hasAllAccess = in_array('all', $currentPermissions);
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <!-- ALL ACCESS CHECKBOX -->
                <label class="flex items-center space-x-3 p-3 border border-blue-200 bg-blue-50 rounded-lg hover:bg-blue-100 cursor-pointer md:col-span-2 transition-colors">
                    <input type="checkbox" name="permissions[]" value="all" id="all_access"
                           class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                           {{ $hasAllAccess ? 'checked' : '' }}>
                    <span class="font-bold text-blue-800">{{ __('messages.full_access') }}</span>
                </label>

                <!-- SPECIFIC PERMISSIONS -->
                @foreach($availablePermissions as $key => $label)
                    <label class="flex items-center space-x-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}" class="permission-item w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                               {{ (in_array($key, $currentPermissions) && !$hasAllAccess) ? 'checked' : '' }}>
                        <span class="font-medium text-gray-700">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow">
            {{ __('messages.update_staff') }}
        </button>
        <a href="{{ route('provider.staff.index') }}" class="ml-2 text-gray-600 hover:underline">{{ __('messages.cancel') }}</a>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allAccess = document.getElementById('all_access');
        const items = document.querySelectorAll('.permission-item');

        if(allAccess) {
            allAccess.addEventListener('change', function() {
                if(this.checked) {
                    items.forEach(item => item.checked = false);
                }
            });
        }
        items.forEach(item => {
            item.addEventListener('change', function() {
                if(this.checked && allAccess) {
                    allAccess.checked = false;
                }
            });
        });
    });
</script>
@endpush
@endsection