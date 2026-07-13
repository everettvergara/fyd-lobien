@props([
    'listing',
    'compact' => false,
])

@php
    use App\Modules\PropertyListings\Support\ListingBrochureTypes;
@endphp

@can('view', $listing)
    <div @class(['listing-brochure-shortcuts d-inline-flex flex-wrap gap-1 align-items-center', 'listing-brochure-shortcuts--compact' => $compact])>
        @foreach (ListingBrochureTypes::definitions() as $type => $definition)
            <a href="{{ ListingBrochureTypes::url($listing, $type) }}"
               class="btn btn-sm btn-outline-secondary admin-icon-btn"
               title="{{ $definition['label'] }}"
               aria-label="{{ $definition['label'] }} brochure"
               target="_blank"
               rel="noopener">
                <i class="{{ admin_icon($definition['icon']) }}" aria-hidden="true"></i>
            </a>
        @endforeach
    </div>
@endcan

@once
    @push('styles')
    <style>
        .listing-brochure-shortcuts--compact .admin-icon-btn {
            padding: 0.2rem 0.35rem;
        }
    </style>
    @endpush
@endonce
