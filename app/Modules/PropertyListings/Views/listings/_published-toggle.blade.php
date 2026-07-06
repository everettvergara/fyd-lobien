@props([
    'listing',
    'iconOnly' => false,
])

@can('update', $listing)
    @if ($iconOnly)
        <input type="checkbox"
               class="btn-check"
               id="listing-published-{{ $listing->id }}"
               data-listing-published-toggle
               data-url="{{ route('admin.listings.published', $listing) }}"
               data-listing-id="{{ $listing->id }}"
               aria-label="Publish to PUBLIC"
               autocomplete="off"
               @checked($listing->published_to_public)>
        <label class="btn btn-sm border-0 shadow-none listing-published-icon-toggle {{ $listing->published_to_public ? 'text-success' : 'text-secondary opacity-50' }}"
               for="listing-published-{{ $listing->id }}"
               title="Publish to PUBLIC"
               data-listing-published-label>
            <i class="{{ admin_icon($listing->published_to_public ? 'bi-check-square-fill' : 'bi-square') }}"
               data-published-icon="{{ admin_icon('bi-check-square-fill') }}"
               data-unpublished-icon="{{ admin_icon('bi-square') }}"
               aria-hidden="true"></i>
            <span class="visually-hidden">Publish to PUBLIC</span>
        </label>
    @else
        <div class="form-check form-switch d-inline-flex align-items-center justify-content-center mb-0">
            <input type="checkbox"
                   class="form-check-input"
                   role="switch"
                   id="listing-published-{{ $listing->id }}"
                   data-listing-published-toggle
                   data-url="{{ route('admin.listings.published', $listing) }}"
                   data-listing-id="{{ $listing->id }}"
                   aria-label="Publish to PUBLIC"
                   @checked($listing->published_to_public)>
        </div>
    @endif
@else
    @if ($listing->published_to_public)
        <span class="badge bg-success-subtle text-success-emphasis">Yes</span>
    @else
        <span class="badge bg-secondary-subtle text-secondary-emphasis">No</span>
    @endif
@endcan
