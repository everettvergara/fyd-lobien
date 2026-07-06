@extends('propertylistings::brochures._layout')

@section('brochure-content')
    @include('propertylistings::brochures._image-pages', [
        'listing' => $listing,
        'pages' => $pages,
        'emptyLabel' => 'No building photos uploaded',
        'emptyIcon' => 'bi-building',
    ])
@endsection
