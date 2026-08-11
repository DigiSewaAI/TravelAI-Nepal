@extends('layouts.admin')

@section('title', 'Reports | TravelAI Nepal')
@section('header', 'Reports')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="{{ route('admin.reports.bookings') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-lg transition">
        <div class="text-center">
            <i class="fas fa-calendar-check text-4xl text-blue-600 mb-4"></i>
            <h3 class="text-lg font-bold text-gray-800">Booking Reports</h3>
            <p class="text-gray-500 text-sm">View booking statistics and trends</p>
        </div>
    </a>

    <a href="{{ route('admin.reports.payments') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-lg transition">
        <div class="text-center">
            <i class="fas fa-credit-card text-4xl text-green-600 mb-4"></i>
            <h3 class="text-lg font-bold text-gray-800">Payment Reports</h3>
            <p class="text-gray-500 text-sm">Revenue and payment analytics</p>
        </div>
    </a>

    <a href="{{ route('admin.reports.providers') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-lg transition">
        <div class="text-center">
            <i class="fas fa-building text-4xl text-purple-600 mb-4"></i>
            <h3 class="text-lg font-bold text-gray-800">Provider Reports</h3>
            <p class="text-gray-500 text-sm">Provider verification and statistics</p>
        </div>
    </a>
</div>
@endsection