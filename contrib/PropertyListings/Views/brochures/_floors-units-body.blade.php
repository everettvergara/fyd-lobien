@php
    use App\Modules\PropertyListings\Support\ListingCompareFormatter;
    use App\Modules\PropertyListings\Support\ListingLookupGroups;

    $lookupLabel = fn (string $group, mixed $value): string => app(\App\Modules\PropertyListings\Services\ListingBrochureService::class)->lookupLabel($group, $value);
@endphp

<div class="listing-brochure-section-bar">Available Floors / Units</div>

<div class="listing-brochure-units-layout">
    <div class="listing-brochure-units-main">
        <table class="listing-brochure-units-table">
            <thead>
                <tr>
                    <th>Floor / Unit</th>
                    <th>Area (m²)</th>
                    <th>No. of Bedrooms</th>
                    <th>Rental Rate (PHP/m2)</th>
                    <th>Selling Price (PHP/m2)</th>
                    <th>Handover Condition*</th>
                    <th>Availability</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($listing->units as $unit)
                    <tr>
                        <td>{{ trim(($unit->floor ?: '').'/'.($unit->unit ?: ''), '/') ?: '—' }}</td>
                        <td>{{ ListingCompareFormatter::area($unit->area_size) }}</td>
                        <td>{{ $lookupLabel(ListingLookupGroups::BEDROOMS, $unit->bedrooms) }}</td>
                        <td>{{ ListingCompareFormatter::money($unit->rental) }}</td>
                        <td>{{ ListingCompareFormatter::money($unit->selling_price) }}</td>
                        <td>{{ $lookupLabel(ListingLookupGroups::HANDOVER_CONDITION, $unit->handover_condition) }}</td>
                        <td>{{ $lookupLabel(ListingLookupGroups::AVAILABILITY, $unit->availability) }}</td>
                        <td>{{ $unit->last_remarks ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No units recorded</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <aside class="listing-brochure-other-rates">
        <div class="listing-brochure-section-bar listing-brochure-section-bar--left">Other Rates</div>
        <ul class="listing-brochure-other-rates__list">
            @forelse ($listing->fees as $fee)
                <li>
                    <span>{{ $lookupLabel(ListingLookupGroups::FEE_TYPE, $fee->fee_type) }}</span>
                    <strong>{{ ListingCompareFormatter::money($fee->fee) }}</strong>
                </li>
            @empty
                <li><span class="text-muted">No fees</span></li>
            @endforelse
        </ul>
    </aside>
</div>

<div class="listing-brochure-notes">
    @include('propertylistings::brochures._handover-key')
    @include('propertylistings::brochures._disclaimer')
</div>
