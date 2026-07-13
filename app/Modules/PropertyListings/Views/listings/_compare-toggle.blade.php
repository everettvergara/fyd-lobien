@props([
    'listing',
    'class' => 'btn btn-sm btn-outline-secondary admin-icon-btn',
])

<button type="button"
        class="{{ $class }}"
        data-listing-compare
        data-listing-id="{{ $listing->id }}"
        data-listing-code="{{ $listing->code }}"
        data-listing-name="{{ $listing->name }}"
        title="Add to compare bin">
    <i class="{{ admin_icon('bi-intersect') }}" aria-hidden="true"></i>
</button>
