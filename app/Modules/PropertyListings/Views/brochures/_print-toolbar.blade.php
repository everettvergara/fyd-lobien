@props([
    'listing',
    'brochureLabel',
])

<div class="listing-brochure-toolbar mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div>
        <a href="{{ route('admin.listings.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="{{ admin_icon('bi-arrow-left') }} me-1" aria-hidden="true"></i>
            Back to Listings
        </a>
        <a href="{{ route('admin.listings.edit', $listing) }}" class="btn btn-outline-secondary btn-sm ms-1">
            Edit Listing
        </a>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="small text-muted">{{ $listing->code }} — {{ $brochureLabel }}</span>
        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="{{ admin_icon('bi-printer') }} me-1" aria-hidden="true"></i>
            Print
        </button>
    </div>
</div>
