@props([
    'fees',
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
@endphp

<table class="table table-sm table-bordered mb-0 listing-compare-fees-table">
    <thead>
        <tr class="small text-muted">
            <th>Fee Type</th>
            <th class="text-end">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($fees as $fee)
            <tr class="small">
                <td>{{ $lookupLabel($lookups, ListingLookupGroups::FEE_TYPE, $fee->fee_type) }}</td>
                <td class="text-end">{{ ListingCompareFormatter::money($fee->fee) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="small text-muted">No fees</td>
            </tr>
        @endforelse
    </tbody>
</table>
