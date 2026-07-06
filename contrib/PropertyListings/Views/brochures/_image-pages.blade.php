@props([
    'pages',
    'emptyLabel' => 'No images uploaded',
    'emptyIcon' => 'bi-image',
])

@foreach ($pages as $page)
    @component('propertylistings::brochures._page', ['listing' => $listing])
        @php
            $layout = $page['layout'] ?? 'hero';
            $images = $page['images'] ?? [];
            $layoutClass = match ($layout) {
                'pair' => 'listing-brochure-images--pair',
                'triple' => 'listing-brochure-images--triple',
                'grid-2x2' => 'listing-brochure-images--grid-2x2',
                'stack' => 'listing-brochure-images--stack',
                'cinematic' => 'listing-brochure-images--cinematic',
                'plan' => 'listing-brochure-images--plan',
                default => 'listing-brochure-images--hero',
            };
            $cinematicBg = ($layout === 'cinematic' && count($images) === 1)
                ? "background-image: url('{$images[0]['full']}');"
                : '';
        @endphp

        @if ($layout === 'empty' || $images === [])
            <div class="listing-brochure-placeholder">
                <i class="{{ admin_icon($emptyIcon) }}" aria-hidden="true"></i>
                <span>{{ $emptyLabel }}</span>
            </div>
        @else
            <div class="listing-brochure-images {{ $layoutClass }}" @if ($cinematicBg !== '') style="{{ $cinematicBg }}" @endif>
                @if ($layout === 'cinematic')
                    <style>
                        .listing-brochure-images--cinematic::before,
                        .listing-brochure-images--cinematic::after {
                            background-image: url('{{ $images[0]['full'] }}');
                        }
                    </style>
                @endif

                @foreach ($images as $image)
                    <div class="listing-brochure-image-frame">
                        <img src="{{ $image['full'] }}" alt="{{ $image['alt'] }}">
                    </div>
                @endforeach
            </div>
        @endif
    @endcomponent
@endforeach
