@props([
    'listing',
    'thumbnailUrl' => null,
])

@php
    $imageUrl = $thumbnailUrl
        ?? $listing->thumbnail_url
        ?? $listing->building_image_url
        ?? null;
@endphp

<div class="card h-100 border-0 shadow-sm listing-thumbnail-card">
    @if ($imageUrl)
        <img src="{{ $imageUrl }}" class="card-img-top" alt="{{ $listing->name }}" style="height:140px;object-fit:cover;">
    @else
        <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-muted small" style="height:140px;">
            <i class="{{ admin_icon('bi-building') }} fs-3" aria-hidden="true"></i>
        </div>
    @endif
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
            <div class="min-w-0">
                <div class="fw-semibold text-truncate" title="{{ $listing->name }}">{{ $listing->name }}</div>
                <div class="small text-muted"><code>{{ $listing->code }}</code></div>
            </div>
            @include('propertylistings::listings._compare-toggle', [
                'listing' => $listing,
                'class' => 'btn btn-sm btn-outline-secondary flex-shrink-0',
            ])
        </div>
        <div class="small text-muted mb-2">
            {{ $listing->city }}{{ $listing->province ? ', '.$listing->province : '' }}
        </div>
        @if ($listing->spec?->developer)
            <div class="small mb-2">{{ $listing->spec->developer }}</div>
        @endif
        <div class="d-flex flex-wrap gap-1">
            @can('update', $listing)
                <a href="{{ route('admin.listings.edit', $listing) }}" class="btn btn-sm btn-primary">Edit</a>
            @endcan
            @can('view', $listing)
                <a href="{{ route('admin.listings.edit', $listing) }}" class="btn btn-sm btn-outline-secondary">View</a>
            @endcan
            @can('delete', $listing)
                <form method="POST"
                      action="{{ route('admin.listings.destroy', $listing) }}"
                      class="d-inline"
                      onsubmit="return confirm('Delete this listing and all related units, fees, assets, and remarks?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="{{ admin_icon('bi-trash') }}" aria-hidden="true"></i>
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
