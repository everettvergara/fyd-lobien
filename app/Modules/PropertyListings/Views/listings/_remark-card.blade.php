@props([
    'listing',
    'remark',
])

@php
    $remarkUser = $remark->user;
    $avatarUrl = $remarkUser?->avatarUrl();
    $initial = strtoupper((string) \Illuminate\Support\Str::of($remarkUser?->name ?? 'U')->substr(0, 1));
@endphp

<div class="listing-remark-card card border-0 shadow-sm mb-2 position-relative"
     data-remark-item
     data-unit-id="{{ $remark->listing_unit_id ?? '' }}">
    @can('update', $listing)
        <form method="POST"
              action="{{ route('admin.listings.remarks.destroy', [$listing, $remark]) }}"
              class="position-absolute top-0 end-0 m-1"
              onsubmit="return confirm('Delete this remark?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="btn btn-sm btn-link text-danger p-0 lh-1 border-0 shadow-none"
                    title="Delete remark"
                    aria-label="Delete remark">
                <i class="{{ admin_icon('bi-trash') }}" aria-hidden="true"></i>
            </button>
        </form>
    @endcan
    <div class="card-body p-2 small">
        <div class="d-flex justify-content-between gap-2 mb-1 pe-4">
            <div class="d-flex align-items-center gap-2 min-w-0">
                @if ($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="{{ $remarkUser?->name ?? 'User' }}" class="listing-remark-avatar">
                @else
                    <span class="listing-remark-avatar listing-remark-avatar-fallback">{{ $initial }}</span>
                @endif
                <strong class="text-truncate">{{ $remarkUser?->name ?? 'User' }}</strong>
            </div>
            <span class="listing-remarks-muted text-nowrap">{{ $remark->remarked_at?->format('M j, Y g:i A') }}</span>
        </div>
        @if ($remark->unit)
            <div class="listing-remarks-muted mb-1">
                Unit: {{ trim(($remark->unit->floor ? 'Fl. '.$remark->unit->floor.' — ' : '').($remark->unit->unit ?: '#'.$remark->unit->id)) }}
            </div>
        @endif
        <div>{{ $remark->comment }}</div>
    </div>
</div>
