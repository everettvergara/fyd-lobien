@props([
    'result',
    'resetRoute' => url()->current(),
])

@php
    $definition = $result->definition;
    $state = $result->state;
    $listingFilterKeys = ['province', 'city', 'completion_status', 'grade', 'developer'];
    $unitFilterKeys = [
        'unit_property_type',
        'unit_availability',
        'unit_handover',
        'unit_bedrooms',
        'unit_for_lease',
        'unit_for_sale',
        'unit_rental_min',
        'unit_rental_max',
        'unit_area_min',
        'unit_area_max',
        'unit_floor',
    ];

    $filtersByKey = collect($definition->filters)->keyBy(fn ($filter) => $filter->key);

    $activeFilterCount = collect($state->filters)
        ->filter(fn ($value) => $value !== null && $value !== '')
        ->count();
@endphp

<div class="listings-filters-toolbar border-bottom" data-listings-filters>
    <form method="GET" class="p-3" data-admin-list-search-form>
        @foreach (request()->except(['search', 'per_page', 'sort', 'direction', ...$listingFilterKeys, ...$unitFilterKeys]) as $key => $value)
            @if (is_array($value))
                @foreach ($value as $item)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        <div class="d-flex flex-wrap gap-2 align-items-end">
            @if ($definition->hasSearch())
                <div class="admin-list-filter-field flex-grow-1" style="min-width:12rem;max-width:20rem;">
                    <label class="form-label small mb-1" for="{{ $definition->id }}-search">Search</label>
                    <input
                        id="{{ $definition->id }}-search"
                        type="search"
                        name="search"
                        value="{{ $state->search }}"
                        class="form-control form-control-sm"
                        placeholder="{{ $definition->searchPlaceholder ?? 'Search...' }}"
                        data-admin-list-search
                    >
                </div>
            @endif

            <div class="admin-list-filter-field">
                <button type="button"
                        class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                        data-listings-filters-toggle
                        aria-expanded="false"
                        aria-controls="listings-filters-panel">
                    <i class="{{ admin_icon('bi-sliders') }}" aria-hidden="true"></i>
                    Filters
                    @if ($activeFilterCount > 0)
                        <span class="badge bg-primary rounded-pill">{{ $activeFilterCount }}</span>
                    @endif
                </button>
            </div>

            <div class="admin-list-filter-field">
                <label class="form-label small mb-1" for="{{ $definition->id }}-per-page">Per page</label>
                <select id="{{ $definition->id }}-per-page" name="per_page" class="form-select form-select-sm">
                    @foreach ($definition->perPageOptions as $option)
                        <option value="{{ $option }}" @selected($state->perPage === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <input type="hidden" name="sort" value="{{ $state->sort }}">
            <input type="hidden" name="direction" value="{{ $state->direction }}">

            <div class="admin-list-filter-actions d-flex gap-2 ms-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Apply</button>
                <a href="{{ $resetRoute }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </div>

        <div id="listings-filters-panel"
             class="listings-filters-panel collapse mt-3 pt-3 border-top"
             data-listings-filters-panel>
            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="small fw-semibold text-muted mb-2">Listing filters</div>
                    <div class="row g-2">
                        @foreach ($listingFilterKeys as $key)
                            @php $filter = $filtersByKey->get($key); @endphp
                            @if ($filter)
                                <div class="col-md-6">
                                    <label class="form-label small mb-1" for="{{ $definition->id }}-{{ $filter->key }}">{{ $filter->label }}</label>
                                    @if ($filter->type === 'select')
                                        <select id="{{ $definition->id }}-{{ $filter->key }}" name="{{ $filter->key }}" class="form-select form-select-sm">
                                            <option value="">All</option>
                                            @foreach ($filter->options() as $value => $label)
                                                <option value="{{ $value }}" @selected(($state->filters[$filter->key] ?? '') == $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input
                                            id="{{ $definition->id }}-{{ $filter->key }}"
                                            type="{{ $filter->type }}"
                                            name="{{ $filter->key }}"
                                            value="{{ $state->filters[$filter->key] ?? '' }}"
                                            class="form-control form-control-sm"
                                        >
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="small fw-semibold text-muted mb-2">Unit filters</div>
                    <div class="row g-2">
                        @foreach ($unitFilterKeys as $key)
                            @php $filter = $filtersByKey->get($key); @endphp
                            @if ($filter)
                                <div class="col-md-6">
                                    <label class="form-label small mb-1" for="{{ $definition->id }}-{{ $filter->key }}">{{ $filter->label }}</label>
                                    @if ($filter->type === 'select')
                                        <select id="{{ $definition->id }}-{{ $filter->key }}" name="{{ $filter->key }}" class="form-select form-select-sm">
                                            <option value="">All</option>
                                            @foreach ($filter->options() as $value => $label)
                                                <option value="{{ $value }}" @selected(($state->filters[$filter->key] ?? '') == $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input
                                            id="{{ $definition->id }}-{{ $filter->key }}"
                                            type="{{ $filter->type }}"
                                            name="{{ $filter->key }}"
                                            value="{{ $state->filters[$filter->key] ?? '' }}"
                                            class="form-control form-control-sm"
                                        >
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@once
    @push('styles')
    <style>
        .listings-filters-toolbar .listings-filters-panel:not(.show) {
            display: none;
        }

        .listings-filters-toolbar .listings-filters-panel.show {
            display: block;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const STORAGE_KEY = 'listings_filters_open';
        const toggle = document.querySelector('[data-listings-filters-toggle]');
        const panel = document.querySelector('[data-listings-filters-panel]');

        if (!toggle || !panel) {
            return;
        }

        const setOpen = (open) => {
            panel.classList.toggle('show', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            sessionStorage.setItem(STORAGE_KEY, open ? '1' : '0');
        };

        const stored = sessionStorage.getItem(STORAGE_KEY);
        const defaultOpen = stored === null ? {{ $activeFilterCount > 0 ? 'true' : 'false' }} : stored === '1';
        setOpen(defaultOpen);

        toggle.addEventListener('click', () => {
            setOpen(!panel.classList.contains('show'));
        });
    });
    </script>
    @endpush
@endonce
