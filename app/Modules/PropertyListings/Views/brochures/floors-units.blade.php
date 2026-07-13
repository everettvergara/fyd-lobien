@extends('propertylistings::brochures._layout')

@section('brochure-content')
    @component('propertylistings::brochures._page', ['listing' => $listing])
        @include('propertylistings::brochures._floors-units-body', ['listing' => $listing])
    @endcomponent
@endsection
