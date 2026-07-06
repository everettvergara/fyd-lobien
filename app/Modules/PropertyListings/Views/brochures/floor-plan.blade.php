@extends('propertylistings::brochures._layout')

@section('brochure-content')
    @include('propertylistings::brochures._image-pages', [
        'listing' => $listing,
        'pages' => $pages,
        'emptyLabel' => 'No floor plan images uploaded',
        'emptyIcon' => 'bi-grid-3x3',
    ])
@endsection
