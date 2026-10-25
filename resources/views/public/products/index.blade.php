@extends('layouts.public')

@php
    $typeTitles = [
        'shop'      => __('messages.shop_index_title'),
        'rental'    => __('messages.rental_index_title'),
        'wholesale' => __('messages.wholesale_index_title'),
    ];
    $typeSubtitles = [
        'shop'      => __('messages.shop_index_subtitle'),
        'rental'    => __('messages.rental_index_subtitle'),
        'wholesale' => __('messages.wholesale_index_subtitle'),
    ];
@endphp

@section('title', ($typeTitles[$type] ?? '') . ' | ' . __('messages.app_name'))
@section('meta_description', $typeSubtitles[$type] ?? '')

@section('content')

<!-- Background pattern (Subtle) -->
<div class="bg-gray-50/50 min-h-screen pt-8 pb-20">
    <section class="max-w-7xl mx-auto px-6 md:px-10">

        <!-- Header Section -->
        <div class="mb-12 border-b border-gray-200 pb-8">
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-tight">{{ $typeTitles[$type] ?? '' }}</h1>
            <p class="text-lg text-gray-500 mt-3 font-medium max-w-2xl">{{ $typeSubtitles[$type] ?? '' }}</p>
        </div>

        <!-- Main Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 xl:gap-12 items-start">

            <!-- Sidebar (Filters) -->
            <aside class="lg:col-span-1">
                @include('public.products._filters', ['type' => $type, 'locations' => $locations])
            </aside>

            <!-- Product Grid Area -->
            <main class="lg:col-span-3">
                @if($products->count() > 0)
                    
                    <!-- Count & Active Filters bar -->
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-sm font-semibold text-gray-500">Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of {{ $products->total() }} results</span>
                    </div>

                    <!-- Card Grid (gap-8 for premium spacing) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8">
                        @foreach($products as $product)
                            @include('public.products._card', ['product' => $product])
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-12 flex justify-center">
                        {{ $products->links() }}
                    </div>

                @else
                    <!-- Premium Empty State -->
                    <div class="flex flex-col items-center justify-center py-24 bg-white rounded-3xl border border-dashed border-gray-300 shadow-sm">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-5">
                            <i class="fas fa-search text-3xl text-gray-300"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No products found</h3>
                        <p class="text-gray-500 text-sm max-w-md text-center mb-6">{{ __('messages.products_no_items') }} Try adjusting your filters or search terms.</p>
                        <a href="{{ url()->current() }}" class="px-6 py-2.5 bg-gray-900 hover:bg-black text-white text-sm font-bold rounded-xl transition-all shadow-md">
                            Clear all filters
                        </a>
                    </div>
                @endif
            </main>

        </div>
    </section>
</div>

@endsection