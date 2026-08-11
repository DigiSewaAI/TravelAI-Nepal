@extends('layouts.admin')

@section('title', $title . ' | TravelAI Nepal')
@section('header', $title)

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-12 text-center">
    <i class="fas fa-construction text-6xl text-yellow-500 mb-4"></i>
    <h2 class="text-2xl font-bold text-gray-800">{{ $title }}</h2>
    <p class="text-gray-500 mt-2">This module is under construction. Coming soon!</p>
    <a href="{{ route('admin.dashboard') }}" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
    </a>
</div>
@endsection