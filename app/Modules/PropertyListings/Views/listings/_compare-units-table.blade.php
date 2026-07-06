@props([
    'units',
    'lookups',
])

@php
    use App\Modules\PropertyListings\Support\ListingCompareFormatter;
    use App\Modules\PropertyListings\Support\ListingLookupGroups;

    $lookupLabel = static function (array $lookups, string $group, mixed $value): string {
        if ($value === null || $value === '') {
            return '—';
        }

        return $lookups[$group][$value] ?? (string) $value;
    };

    $yesNo = static fn (mixed $value): string => $value ? 'Yes' : '—';
@endphp

<table class="table table-sm table-bordered mb-0 listing-compare-units-table">
    <thead>
        <tr class="small text-muted">
            <th title="Floor">Fl</th>
            <th title="Unit">Unit</th>
            <th title="Area Size" class="text-end">Area</th>
            <th title="Rental" class="text-end">Rent</th>
            <th title="Handover Condition">HO</th>
            <th title="Availability">Avl</th>
            <th title="Bedrooms">Bds</th>
            <th title="Selling Price" class="text-end">Price</th>
            <th title="Property Type" class="listing-compare-units-type">Type</th>
            <th title="For Lease" class="text-center text-nowrap">Lease</th>
            <th title="For Sale" class="text-center text-nowrap">Sale</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($units as $unit)
            <tr class="small"
                data-listing-compare-unit-row
                data-unit-area="{{ $unit->area_size ?? '' }}"
                data-unit-rental="{{ $unit->rental ?? '' }}"
                data-unit-price="{{ $unit->selling_price ?? '' }}"
                data-unit-ho="{{ $unit->handover_condition ?? '' }}"
                data-unit-avl="{{ $unit->availability ?? '' }}"
                data-unit-bds="{{ $unit->bedrooms ?? '' }}"
                data-unit-type="{{ $unit->property_type ?? '' }}"
                data-unit-lease="{{ $unit->for_lease ? '1' : '0' }}"
                data-unit-sale="{{ $unit->for_sale ? '1' : '0' }}">
                <td>{{ $unit->floor ?: '—' }}</td>
                <td>{{ $unit->unit ?: '—' }}</td>
                <td class="text-end">{{ ListingCompareFormatter::area($unit->area_size) }}</td>
                <td class="text-end">{{ ListingCompareFormatter::money($unit->rental) }}</td>
                <td>{{ $lookupLabel($lookups, ListingLookupGroups::HANDOVER_CONDITION, $unit->handover_condition) }}</td>
                <td>{{ $lookupLabel($lookups, ListingLookupGroups::AVAILABILITY, $unit->availability) }}</td>
                <td>{{ $lookupLabel($lookups, ListingLookupGroups::BEDROOMS, $unit->bedrooms) }}</td>
                <td class="text-end">{{ ListingCompareFormatter::money($unit->selling_price) }}</td>
                <td class="listing-compare-units-type">{{ $lookupLabel($lookups, ListingLookupGroups::PROPERTY_TYPE, $unit->property_type) }}</td>
                <td class="text-center">{{ $yesNo($unit->for_lease) }}</td>
                <td class="text-center">{{ $yesNo($unit->for_sale) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="small text-muted">No units</td>
            </tr>
        @endforelse
        @if ($units->isNotEmpty())
            <tr class="small text-muted d-none" data-listing-compare-unit-no-match>
                <td colspan="11">No matching units</td>
            </tr>
        @endif
    </tbody>
</table>
