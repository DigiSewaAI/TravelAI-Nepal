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

<section class="max-w-7xl mx-auto px-6 md:px-10 py-8">

    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900">{{ $typeTitles[$type] ?? '' }}</h1>
        <p class="text-gray-600 mt-2">{{ $typeSubtitles[$type] ?? '' }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        <aside class="lg:col-span-1">
            @include('public.products._filters', ['type' => $type, 'locations' => $locations])
        </aside>

        <main class="lg:col-span-3">
            @if($products->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($products as $product)
                        @include('public.products._card', ['product' => $product])
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-16 bg-gray-50 rounded-xl">
                    <p class="text-gray-500">{{ __('messages.products_no_items') }}</p>
                </div>
            @endif
        </main>

    </div>
</section>

@endsection