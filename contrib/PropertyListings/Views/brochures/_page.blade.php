@props([
    'listing',
])

<div class="listing-brochure-page">
    @include('propertylistings::brochures._header', ['listing' => $listing])

    <div class="listing-brochure-body">
        {{ $slot }}
    </div>

    @include('propertylistings::brochures._footer')
</div>
