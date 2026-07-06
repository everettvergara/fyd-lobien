@extends('propertylistings::brochures._layout')

@section('brochure-content')
    @component('propertylistings::brochures._page', ['listing' => $listing])
        @include('propertylistings::brochures._property-information-body', [
            'buildingImages' => $buildingImages,
            'mapImages' => $mapImages,
            'propertyInfoRows' => $propertyInfoRows,
        ])
    @endcomponent
@endsection
