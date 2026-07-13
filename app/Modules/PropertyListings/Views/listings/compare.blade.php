@extends('admin.layouts.app')

@section('title', 'Compare Listings')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Listings', 'url' => route('admin.listings.index')],
        ['label' => 'Compare'],
    ]" />

    <x-admin.page-header
        title="Compare Listings"
        :back-route="route('admin.listings.index')"
        back-label="Back to Listings"
    >
        <x-slot:actions>
            <button type="button" class="btn btn-outline-secondary d-print-none" onclick="window.print()">
                <i class="{{ admin_icon('bi-printer') }} me-1" aria-hidden="true"></i>
                Print
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($listings->count() < 2)
        <div class="alert alert-info">
            Add at least two listings to the compare bin from the list page, then open compare again.
        </div>
    @else
        <div class="listing-compare-print">
            @include('propertylistings::listings._compare-unit-filter', ['lookups' => $lookups])
            <div class="table-responsive listing-compare-responsive">
                <table class="table table-bordered align-middle mb-0 bg-white listing-compare-table">
                    <colgroup>
                        <col class="listing-compare-label-col">
                        @foreach ($listings as $listing)
                            <col class="listing-compare-listing-col">
                        @endforeach
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th class="listing-compare-label-col align-middle small text-muted fw-normal">Building</th>
                            @foreach ($listings as $listing)
                                <th class="listing-compare-listing-col p-2 align-top fw-normal">
                                    @include('propertylistings::listings._compare-listing-carousel', [
                                        'listing' => $listing,
                                        'assetType' => 'building',
                                        'placeholderIcon' => 'bi-building',
                                        'carouselSuffix' => 'building',
                                        'sectionLabel' => 'Building',
                                    ])
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="listing-compare-label-col align-middle small text-muted fw-normal">Floor Plan</th>
                            @foreach ($listings as $listing)
                                <th class="listing-compare-listing-col p-2 align-top fw-normal">
                                    @include('propertylistings::listings._compare-listing-carousel', [
                                        'listing' => $listing,
                                        'assetType' => 'floor-plan',
                                        'placeholderIcon' => 'bi-map',
                                        'carouselSuffix' => 'floor-plan',
                                        'sectionLabel' => 'Floor Plan',
                                    ])
                                </th>
                            @endforeach
                        </tr>
                        @include('propertylistings::listings._compare-location-section', ['listings' => $listings])
                        @include('propertylistings::listings._compare-units-section', [
                            'listings' => $listings,
                            'lookups' => $lookups,
                        ])
                        @include('propertylistings::listings._compare-fees-section', [
                            'listings' => $listings,
                            'lookups' => $lookups,
                        ])
                        <tr>
                            <th class="listing-compare-label-col">Field</th>
                            @foreach ($listings as $listing)
                                <th class="listing-compare-listing-col">
                                    <div class="fw-semibold">{{ $listing->name }}</div>
                                    <div class="small text-muted"><code>{{ $listing->code }}</code></div>
                                    @can('update', $listing)
                                        <a href="{{ route('admin.listings.edit', $listing) }}" class="small d-print-none">Edit</a>
                                    @endcan
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rows = [
                                'Identity' => [
                                    'Code' => fn ($l) => $l->code,
                                    'Name' => fn ($l) => $l->name,
                                    'Completion Status' => fn ($l) => $lookups[\App\Modules\PropertyListings\Support\ListingLookupGroups::COMPLETION_STATUS][$l->completion_status] ?? $l->completion_status ?? '—',
                                ],
                                'Rates & Sizing' => [
                                    'Office Rental Rate' => fn ($l) => \App\Modules\PropertyListings\Support\ListingCompareFormatter::money($l->office_rental_rate),
                                    'Total Area Size' => fn ($l) => \App\Modules\PropertyListings\Support\ListingCompareFormatter::area($l->total_area_size),
                                    'Unit Market Size' => fn ($l) => \App\Modules\PropertyListings\Support\ListingCompareFormatter::area($l->unit_market_size),
                                    'Retail Market Rate' => fn ($l) => \App\Modules\PropertyListings\Support\ListingCompareFormatter::money($l->retail_market_rate),
                                ],
                                'Specs' => [
                                    'Developer' => fn ($l) => $l->spec?->developer ?? '—',
                                    'Grade' => fn ($l) => $lookups[\App\Modules\PropertyListings\Support\ListingLookupGroups::GRADE][$l->spec?->grade] ?? $l->spec?->grade ?? '—',
                                    'Completion Year' => fn ($l) => $l->spec?->completion_year ?? '—',
                                    'Gross Leasable Area' => fn ($l) => \App\Modules\PropertyListings\Support\ListingCompareFormatter::area($l->spec?->gross_leasable_area),
                                    'Net Usable Area' => fn ($l) => \App\Modules\PropertyListings\Support\ListingCompareFormatter::area($l->netUsableArea()),
                                ],
                                'Building Services' => [
                                    'Operating Hours' => fn ($l) => $l->buildingService?->operating_hours ?? '—',
                                    'AC System' => fn ($l) => $l->buildingService?->ac_system ?? '—',
                                    'Passenger Lifts' => fn ($l) => $l->buildingService?->no_of_lifts_passenger ?? '—',
                                ],
                            ];
                        @endphp

                        @foreach ($rows as $section => $fields)
                            <tr class="table-secondary">
                                <th colspan="{{ $listings->count() + 1 }}">{{ $section }}</th>
                            </tr>
                            @foreach ($fields as $label => $resolver)
                                <tr>
                                    <th class="listing-compare-label-col small text-muted fw-normal">{{ $label }}</th>
                                    @foreach ($listings as $listing)
                                        <td class="listing-compare-listing-col">{{ $resolver($listing) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('propertylistings::listings._compare-disclaimer')
        </div>

        @include('propertylistings::listings._compare-image-preview-modal')
    @endif

    <div class="listing-comparator-bin border-top bg-white shadow-sm position-fixed bottom-0 start-0 end-0 d-none mt-4 d-print-none"
         data-listing-comparator-bin
         data-compare-url="{{ route('admin.listings.compare') }}"
         style="z-index:1030;">
        <div class="container-fluid py-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button"
                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                        data-listing-comparator-open>
                    <i class="{{ admin_icon('bi-intersect') }}" aria-hidden="true"></i>
                    <span>Compare</span>
                    <span class="badge bg-primary rounded-pill" data-listing-comparator-count>0</span>
                </button>
                <div class="d-flex flex-wrap gap-1 flex-grow-1" data-listing-comparator-chips></div>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-listing-comparator-clear>
                    Clear all
                </button>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.listing-compare-print .listing-compare-label-col {
    width: 1%;
    white-space: nowrap;
    vertical-align: top;
}

.listing-compare-print .listing-compare-responsive {
    width: fit-content;
    max-width: 100%;
}

.listing-compare-print .listing-compare-table {
    width: auto !important;
    table-layout: auto;
    max-width: 100%;
}

.listing-compare-print .listing-compare-listing-col {
    width: 1%;
    vertical-align: top;
    padding: 0.375rem 0.5rem;
}

.listing-compare-print .listing-compare-carousel {
    width: 300px;
    max-width: 300px;
    height: auto;
    margin-left: 0;
    margin-right: auto;
}

.listing-compare-print .listing-compare-carousel img {
    width: 100%;
    height: auto;
    display: block;
    object-fit: contain;
}

.listing-compare-print .listing-compare-carousel .carousel-item,
.listing-compare-print .listing-compare-carousel .carousel-inner {
    height: auto;
}

.listing-compare-print .listing-compare-units-table {
    width: auto;
    max-width: 100%;
}

.listing-compare-print .listing-compare-units-table th,
.listing-compare-print .listing-compare-units-table td {
    white-space: nowrap;
}

.listing-compare-print .listing-compare-units-table .listing-compare-units-type {
    white-space: normal;
    max-width: 9rem;
}

.listing-compare-print .listing-compare-fees-table {
    width: auto;
    max-width: 100%;
}

.listing-compare-print .listing-compare-fees-table th,
.listing-compare-print .listing-compare-fees-table td {
    white-space: nowrap;
}

@media print {
    .admin-wrapper > .admin-sidebar,
    .admin-navbar,
    .admin-breadcrumb,
    .listing-comparator-bin,
    .modal,
    .d-print-none,
    .listing-compare-carousel-controls,
    [data-listing-compare-unit-filter] {
        display: none !important;
    }

    .admin-content-wrapper,
    .admin-main {
        padding: 0 !important;
        margin: 0 !important;
    }

    .listing-compare-print {
        width: 100%;
    }

    @page {
        size: landscape;
        margin: 12mm;
    }

    .listing-compare-carousel .carousel-inner {
        display: block !important;
    }

    .listing-compare-carousel .carousel-item {
        display: block !important;
        opacity: 1 !important;
        position: static !important;
        page-break-inside: avoid;
    }

    .listing-compare-carousel .carousel-item img {
        width: 100%;
        height: auto;
        max-height: none;
        object-fit: contain;
        margin-bottom: 0.25rem;
    }

    .listing-compare-print .listing-compare-carousel {
        width: 300px;
        max-width: 300px;
        height: auto;
        margin-left: 0;
        margin-right: auto;
    }

    .listing-compare-print table {
        font-size: 10pt;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .listing-compare-print thead {
        display: table-header-group;
    }

    .listing-compare-print tr {
        page-break-inside: avoid;
    }

    .listing-compare-units-table,
    .listing-compare-fees-table {
        font-size: 8pt;
    }

    .listing-compare-disclaimer {
        page-break-inside: avoid;
        margin-top: 1rem;
    }

    [data-listing-compare-preview] {
        border: none !important;
        padding: 0 !important;
        background: none !important;
    }
}
</style>
@endpush
