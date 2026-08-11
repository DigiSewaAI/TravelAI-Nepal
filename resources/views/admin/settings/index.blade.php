@extends('layouts.admin')

@section('title', 'Settings | TravelAI Nepal')
@section('header', 'Settings')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">System Settings</h2>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf

        <div class="space-y-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Site Name</label>
                <input type="text" name="site_name" value="{{ config('app.name') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Site URL</label>
                <input type="text" name="site_url" value="{{ config('app.url') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Maintenance Mode</label>
                <select name="maintenance" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="0">Disabled</option>
                    <option value="1">Enabled</option>
                </select>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                <i class="fas fa-save mr-1"></i> Update Settings
            </button>
        </div>
    </form>
</div>
@endsection