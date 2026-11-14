@extends('layouts.provider-inventory')
@section('content')
    @include('provider.partials._offers-crud', ['verticalPrefix' => 'inventory'])
@endsection