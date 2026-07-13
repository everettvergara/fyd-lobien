@props([
    'listings',
    'lookups',
])

<tr data-listing-compare-units>
    <th class="listing-compare-label-col align-top small text-muted fw-normal">Units</th>
    @foreach ($listings as $listing)
        <th class="listing-compare-listing-col p-2 align-top fw-normal text-start">
            @include('propertylistings::listings._compare-units-table', [
                'units' => $listing->units,
                'lookups' => $lookups,
            ])
        </th>
    @endforeach
</tr>
