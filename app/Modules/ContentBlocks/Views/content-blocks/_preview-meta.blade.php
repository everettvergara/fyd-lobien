@php
    $meta = $meta ?? [];
@endphp

@if (($meta['totalMatching'] ?? 0) === 0)
    No published content matches the current configuration.
@else
    <strong>{{ number_format($meta['totalMatching']) }}</strong> item(s) match.
    Showing <strong>{{ number_format($meta['retrieved']) }}</strong>
    @if (! empty($meta['paginationEnabled']))
        on page {{ $meta['page'] }} of {{ $meta['lastPage'] }} ({{ $meta['perPage'] }} per page).
    @elseif (! empty($meta['limitedTo']))
        (limited to {{ $meta['limitedTo'] }}).
    @else
        .
    @endif
    Formatter: <strong>{{ $meta['formatter'] ?? 'unformatted' }}</strong>.
@endif
