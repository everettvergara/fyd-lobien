@props([
    'listings',
    'lookups',
])

<tr data-listing-compare-fees>
    <th class="listing-compare-label-col align-top small text-muted fw-normal">Fees</th>
    @foreach ($listings as $listing)
        <th class="listing-compare-listing-col p-2 align-top fw-normal text-start">
            @include('propertylistings::listings._compare-fees-table', [
                'fees' => $listing->fees,
                'lookups' => $lookups,
            ])
        </th>
    @endforeach
</tr>
