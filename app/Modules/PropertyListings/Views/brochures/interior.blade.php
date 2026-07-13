@extends('propertylistings::brochures._layout')

@section('brochure-content')
    @include('propertylistings::brochures._image-pages', [
        'listing' => $listing,
        'pages' => $pages,
        'emptyLabel' => 'No interior images uploaded',
        'emptyIcon' => 'bi-lamp',
    ])
@endsection
