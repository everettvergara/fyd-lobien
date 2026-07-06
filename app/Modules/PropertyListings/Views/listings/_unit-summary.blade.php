@props([
    'listing',
    'compact' => false,
])

@php
    $summary = app(\App\Modules\PropertyListings\Support\ListingUnitSummary::class);
    $propertyTypes = $summary->propertyTypeSummary($listing);
    $availability = $summary->availabilitySummary($listing);
    $forLease = $summary->hasForLease($listing);
    $forSale = $summary->hasForSale($listing);
@endphp

<div class="listing-unit-summary @if ($compact) listing-unit-summary--compact @endif">
    <div class="small text-muted listing-unit-summary__types">{{ $propertyTypes }}</div>
    <div class="small text-muted listing-unit-summary__avl">{{ $availability }}</div>
    @if ($forLease || $forSale)
        <div class="d-flex flex-wrap gap-1 listing-unit-summary__intent @if ($compact) mt-1 @else mt-1 @endif">
            @if ($forLease)
                <span class="badge bg-secondary-subtle text-secondary-emphasis">For Lease</span>
            @endif
            @if ($forSale)
                <span class="badge bg-secondary-subtle text-secondary-emphasis">For Sale</span>
            @endif
        </div>
    @endif
</div>
