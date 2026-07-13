@props([
    'listing',
])

@php
    $buildingUrls = $listing->buildingImageUrls();
    $editUrl = route('admin.listings.edit', $listing);
    $canView = auth()->user()?->can('view', $listing) ?? false;
    $carouselId = 'listing-thumb-carousel-'.$listing->id;
    $unitSummary = app(\App\Modules\PropertyListings\Support\ListingUnitSummary::class);
    $propertyTypes = $unitSummary->propertyTypeSummary($listing);
    $availability = $unitSummary->availabilitySummary($listing);
    $forSale = $unitSummary->hasForSale($listing);
    $forLease = $unitSummary->hasForLease($listing);
    $location = trim(collect([$listing->address, $listing->brgy, $listing->city, $listing->province])->filter()->implode(', '));
@endphp

<div class="card h-100 border-0 shadow-sm listing-thumbnail-card">
    <div class="listing-thumbnail-media">
        <div class="listing-thumbnail-media__frame">
            @if (count($buildingUrls) === 0)
                @if ($canView)
                    <a href="{{ $editUrl }}" class="listing-thumbnail-media__placeholder text-decoration-none">
                        <i class="{{ admin_icon('bi-building') }} fs-3" aria-hidden="true"></i>
                    </a>
                @else
                    <div class="listing-thumbnail-media__placeholder">
                        <i class="{{ admin_icon('bi-building') }} fs-3" aria-hidden="true"></i>
                    </div>
                @endif
            @elseif (count($buildingUrls) === 1)
                @if ($canView)
                    <a href="{{ $editUrl }}" class="listing-thumbnail-media__link">
                        <img src="{{ $buildingUrls[0] }}" class="listing-thumbnail-media__image" alt="{{ $listing->name }}">
                    </a>
                @else
                    <img src="{{ $buildingUrls[0] }}" class="listing-thumbnail-media__image" alt="{{ $listing->name }}">
                @endif
            @else
                <div id="{{ $carouselId }}" class="carousel slide listing-thumbnail-media__carousel" data-bs-ride="false">
                    <div class="carousel-inner h-100">
                        @foreach ($buildingUrls as $index => $imageUrl)
                            <div class="carousel-item h-100 @if ($index === 0) active @endif">
                                @if ($canView)
                                    <a href="{{ $editUrl }}" class="listing-thumbnail-media__link h-100">
                                        <img src="{{ $imageUrl }}" class="listing-thumbnail-media__image" alt="{{ $listing->name }}">
                                    </a>
                                @else
                                    <img src="{{ $imageUrl }}" class="listing-thumbnail-media__image" alt="{{ $listing->name }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                    <div class="carousel-indicators mb-1">
                        @foreach ($buildingUrls as $index => $imageUrl)
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
    </div>
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
            <div class="min-w-0">
                @if ($canView)
                    <a href="{{ $editUrl }}" class="listing-thumbnail-title fw-semibold text-truncate d-block text-dark text-decoration-none" title="{{ $listing->name }}">{{ $listing->name }}</a>
                @else
                    <div class="listing-thumbnail-title fw-semibold text-truncate" title="{{ $listing->name }}">{{ $listing->name }}</div>
                @endif
            </div>
            @include('propertylistings::listings._compare-toggle', [
                'listing' => $listing,
                'class' => 'btn btn-sm btn-outline-secondary flex-shrink-0',
            ])
        </div>
        @if ($listing->spec?->developer)
            <div class="small text-muted mb-2">{{ $listing->spec->developer }}</div>
        @endif

        @if ($forSale || $forLease)
            <div class="d-flex flex-wrap gap-1 mb-2">
                @if ($forSale)
                    <span class="badge bg-secondary-subtle text-secondary-emphasis">For Sale</span>
                @endif
                @if ($forLease)
                    <span class="badge bg-secondary-subtle text-secondary-emphasis">For Lease</span>
                @endif
            </div>
        @endif

        @if ($location !== '')
            <div class="small text-muted mb-3">{{ $location }}</div>
        @endif

        <hr class="my-2">

        <div class="listing-unit-summary">
            <div class="small text-muted">{{ $propertyTypes }}</div>
            <div class="small text-muted mb-2">{{ $availability }}</div>
        </div>

        <hr class="my-2">

        @include('propertylistings::listings._brochure-shortcuts', ['listing' => $listing])

        <div class="d-flex flex-wrap gap-1 align-items-center mt-2">
            @can('update', $listing)
                <a href="{{ route('admin.listings.edit', $listing) }}" class="btn btn-sm btn-primary">Edit</a>
                @include('propertylistings::listings._published-toggle', ['listing' => $listing, 'iconOnly' => true])
            @endcan
            @can('delete', $listing)
                <form method="POST"
                      action="{{ route('admin.listings.destroy', $listing) }}"
                      class="d-inline"
                      onsubmit="return confirm('Delete this listing and all related units, fees, assets, and remarks?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm border-0 text-danger shadow-none" title="Delete">
                        <i class="{{ admin_icon('bi-trash') }}" aria-hidden="true"></i>
                        <span class="visually-hidden">Delete</span>
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>

@once
    @push('styles')
    <style>
        .listing-thumbnail-card {
            width: 100%;
        }

        .listing-thumbnail-card .listing-thumbnail-title {
            font-size: 1rem;
            line-height: 1.2;
        }

        .listing-thumbnail-card .listing-thumbnail-media {
            display: flex;
            justify-content: center;
            padding: 0.75rem 0.75rem 0;
        }

        .listing-thumbnail-card .listing-thumbnail-media__frame {
            width: min(100%, 10rem);
            aspect-ratio: 1 / 1;
            margin-inline: auto;
            overflow: hidden;
            position: relative;
        }

        .listing-thumbnail-card .listing-thumbnail-media__link,
        .listing-thumbnail-card .listing-thumbnail-media__placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .listing-thumbnail-card .listing-thumbnail-media__placeholder {
            background-color: var(--bs-light, #f8f9fa);
            color: var(--bs-secondary-color, #6c757d);
        }

        .listing-thumbnail-card .listing-thumbnail-media__image {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .listing-thumbnail-card .listing-thumbnail-media__carousel,
        .listing-thumbnail-card .listing-thumbnail-media__carousel .carousel-inner,
        .listing-thumbnail-card .listing-thumbnail-media__carousel .carousel-item {
            height: 100%;
        }
    </style>
    @endpush
@endonce
