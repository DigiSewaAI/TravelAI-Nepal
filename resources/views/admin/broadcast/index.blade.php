@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Broadcast Announcement</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4 shadow-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4 shadow-sm">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.broadcast.send') }}" method="POST" class="bg-white p-6 rounded-lg shadow border border-gray-200">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Target Audience</label>
            <select name="audience" class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500" required>
                <option value="all">All Users (Everyone)</option>
                <option value="traveler">Travelers Only</option>
                <option value="provider_owner">Providers Only</option>
                <option value="staff">Staff Only</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">Choose who should receive this email and in-app notification.</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Subject / Title</label>
            <input type="text" name="subject" value="{{ old('subject') }}" class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500" placeholder="e.g., System Maintenance or New Feature Launch" required>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">Message Body</label>
            <textarea name="message" rows="6" class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500" placeholder="Write your announcement message here..." required>{{ old('message') }}</textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded font-semibold hover:bg-indigo-700 shadow-sm flex items-center gap-2">
                <i class="fas fa-paper-plane"></i> Send Announcement
            </button>
        </div>
    </form>
</div>
@endsection