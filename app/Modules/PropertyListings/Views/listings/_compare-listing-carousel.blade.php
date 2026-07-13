@props([
    'listing',
    'assetType',
    'placeholderIcon',
    'carouselSuffix',
    'sectionLabel',
])

@php
    $images = $listing->assetImages($assetType);
    $carouselId = 'listing-compare-carousel-'.$carouselSuffix.'-'.$listing->id;
    $previewTitle = $listing->name.' — '.$sectionLabel;
@endphp

<div class="listing-compare-carousel" data-listing-compare-images="{{ $assetType }}">
    @if (count($images) === 0)
        <div class="bg-light d-flex align-items-center justify-content-center text-muted rounded border" style="min-height:100px;">
            <i class="{{ admin_icon($placeholderIcon) }} fs-2" aria-hidden="true"></i>
        </div>
    @elseif (count($images) === 1)
        <button type="button"
                class="btn p-0 border-0 w-100 d-block"
                data-listing-compare-preview
                data-preview-url="{{ $images[0]['full'] }}"
                data-preview-title="{{ $previewTitle }}"
                aria-label="Preview {{ $sectionLabel }} image">
            <img src="{{ $images[0]['thumb'] }}" class="w-100 rounded border" alt="{{ $images[0]['alt'] }}">
        </button>
    @else
        <div id="{{ $carouselId }}" class="carousel slide" data-bs-ride="false">
            <div class="carousel-inner">
                @foreach ($images as $index => $image)
                    <div class="carousel-item @if ($index === 0) active @endif">
                        <button type="button"
                                class="btn p-0 border-0 w-100 d-block"
                                data-listing-compare-preview
                                data-preview-url="{{ $image['full'] }}"
                                data-preview-title="{{ $previewTitle }}"
                                aria-label="Preview {{ $sectionLabel }} image {{ $index + 1 }}">
                            <img src="{{ $image['thumb'] }}" class="w-100 rounded border" alt="{{ $image['alt'] }}">
                        </button>
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev listing-compare-carousel-controls" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next listing-compare-carousel-controls" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            <div class="carousel-indicators mb-1 listing-compare-carousel-controls">
                @foreach ($images as $index => $image)
                    <button type="button"
                            data-bs-target="#{{ $carouselId }}"
                            data-bs-slide-to="{{ $index }}"
                            @if ($index === 0) class="active" aria-current="true" @endif
                            aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
        </div>
    @endif
</div>
