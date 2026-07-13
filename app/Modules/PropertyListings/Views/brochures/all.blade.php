@extends('propertylistings::brochures._layout')

@section('brochure-content')
    @foreach ($sections as $section)
        @if (in_array($section['type'], ['interior', 'property-photos', 'floor-plan'], true))
            @include('propertylistings::brochures._image-pages', [
                'listing' => $listing,
                'pages' => $section['pages'],
                'emptyLabel' => 'No '.$section['label'].' uploaded',
                'emptyIcon' => \App\Modules\PropertyListings\Support\ListingBrochureTypes::icon($section['type']),
            ])
        @elseif ($section['type'] === 'floors-units')
            @component('propertylistings::brochures._page', ['listing' => $listing])
                @include('propertylistings::brochures._floors-units-body', ['listing' => $listing])
            @endcomponent
        @elseif ($section['type'] === 'property-information')
            @component('propertylistings::brochures._page', ['listing' => $listing])
                @include('propertylistings::brochures._property-information-body', [
                    'buildingImages' => $section['buildingImages'],
                    'mapImages' => $section['mapImages'],
                    'propertyInfoRows' => $section['propertyInfoRows'],
                ])
            @endcomponent
        @endif
    @endforeach
@endsection
