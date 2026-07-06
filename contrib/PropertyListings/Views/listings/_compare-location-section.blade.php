@props([
    'listings',
])

<tr data-listing-compare-location>
    <th class="listing-compare-label-col align-top small text-muted fw-normal">Location</th>
    @foreach ($listings as $listing)
        <th class="listing-compare-listing-col p-2 align-top fw-normal">
            <div class="listing-compare-location-block small">
                <div><span class="fw-semibold">Province:</span> {{ $listing->province ?: '—' }}</div>
                <div><span class="fw-semibold">City:</span> {{ $listing->city ?: '—' }}</div>
                <div><span class="fw-semibold">Barangay:</span> {{ $listing->brgy ?: '—' }}</div>
                <div><span class="fw-semibold">Address:</span> {{ $listing->address ?: '—' }}</div>
            </div>
        </th>
    @endforeach
</tr>
