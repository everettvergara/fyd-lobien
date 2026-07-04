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
    />

    @if ($listings->count() < 2)
        <div class="alert alert-info">
            Add at least two listings to the compare bin from the list page, then open compare again.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 bg-white">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:12rem;">Field</th>
                        @foreach ($listings as $listing)
                            <th style="min-width:14rem;">
                                <div class="fw-semibold">{{ $listing->name }}</div>
                                <div class="small text-muted"><code>{{ $listing->code }}</code></div>
                                @can('update', $listing)
                                    <a href="{{ route('admin.listings.edit', $listing) }}" class="small">Edit</a>
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
                            'Location' => [
                                'Province' => fn ($l) => $l->province,
                                'City' => fn ($l) => $l->city,
                                'Barangay' => fn ($l) => $l->brgy ?? '—',
                                'Address' => fn ($l) => $l->address ?? '—',
                            ],
                            'Rates & Sizing' => [
                                'Office Rental Rate' => fn ($l) => $l->office_rental_rate ?? '—',
                                'Total Area Size' => fn ($l) => $l->total_area_size ?? '—',
                                'Unit Market Size' => fn ($l) => $l->unit_market_size ?? '—',
                                'Retail Market Rate' => fn ($l) => $l->retail_market_rate ?? '—',
                            ],
                            'Specs' => [
                                'Developer' => fn ($l) => $l->spec?->developer ?? '—',
                                'Grade' => fn ($l) => $lookups[\App\Modules\PropertyListings\Support\ListingLookupGroups::GRADE][$l->spec?->grade] ?? $l->spec?->grade ?? '—',
                                'Completion Year' => fn ($l) => $l->spec?->completion_year ?? '—',
                                'Gross Leasable Area' => fn ($l) => $l->spec?->gross_leasable_area ?? '—',
                                'Net Usable Area' => fn ($l) => $l->netUsableArea() ?? '—',
                            ],
                            'Building Services' => [
                                'Operating Hours' => fn ($l) => $l->buildingService?->operating_hours ?? '—',
                                'AC System' => fn ($l) => $l->buildingService?->ac_system ?? '—',
                                'Passenger Lifts' => fn ($l) => $l->buildingService?->no_of_lifts_passenger ?? '—',
                            ],
                            'Counts' => [
                                'Units' => fn ($l) => $l->units_count ?? $l->units?->count() ?? 0,
                                'Fees' => fn ($l) => $l->fees_count ?? $l->fees?->count() ?? 0,
                                'Assets' => fn ($l) => $l->assets_count ?? $l->assets?->count() ?? 0,
                            ],
                        ];
                    @endphp

                    @foreach ($rows as $section => $fields)
                        <tr class="table-secondary">
                            <th colspan="{{ $listings->count() + 1 }}">{{ $section }}</th>
                        </tr>
                        @foreach ($fields as $label => $resolver)
                            <tr>
                                <th class="small text-muted fw-normal">{{ $label }}</th>
                                @foreach ($listings as $listing)
                                    <td>{{ $resolver($listing) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="listing-comparator-bin border-top bg-white shadow-sm position-fixed bottom-0 start-0 end-0 d-none mt-4"
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
