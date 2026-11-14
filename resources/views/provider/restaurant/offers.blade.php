@extends('layouts.provider-restaurant')
@section('content')
    @include('provider.partials._offers-crud', ['verticalPrefix' => 'restaurant'])
@endsection