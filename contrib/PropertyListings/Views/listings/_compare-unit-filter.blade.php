@props([
    'lookups',
])

@php
    use App\Modules\PropertyListings\Support\ListingLookupGroups;
@endphp

<div class="p-3 mb-3 admin-list-toolbar listings-filters-toolbar d-print-none border rounded bg-white"
     data-listing-compare-unit-filter>
    <div class="d-flex flex-wrap gap-3 align-items-end w-100">
        <div class="admin-list-filter-field">
            <button type="button"
                    class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                    data-listing-compare-unit-filter-toggle
                    aria-expanded="false"
                    aria-controls="listing-compare-unit-filter-panel">
                <i class="{{ admin_icon('bi-sliders') }}" aria-hidden="true"></i>
                Unit filter
                <span class="badge bg-primary rounded-pill d-none" data-listing-compare-unit-filter-count>0</span>
            </button>
        </div>

        <div class="admin-list-filter-actions d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-listing-compare-unit-filter-apply>
                Apply
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-listing-compare-unit-filter-reset>
                Reset
            </button>
        </div>
    </div>

    <div id="listing-compare-unit-filter-panel"
         class="listings-filters-panel collapse mt-3 pt-3 border-top"
         data-listing-compare-unit-filter-panel>
        <div class="row g-2">
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1" for="listing-compare-unit-area-min">Area min</label>
                <input type="number"
                       id="listing-compare-unit-area-min"
                       name="area_min"
                       class="form-control form-control-sm"
                       step="0.01"
                       min="0"
                       data-listing-compare-unit-filter-input>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1" for="listing-compare-unit-area-max">Area max</label>
                <input type="number"
                       id="listing-compare-unit-area-max"
                       name="area_max"
                       class="form-control form-control-sm"
                       step="0.01"
                       min="0"
                       data-listing-compare-unit-filter-input>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1" for="listing-compare-unit-rent-min">Rent min</label>
                <input type="number"
                       id="listing-compare-unit-rent-min"
                       name="rent_min"
                       class="form-control form-control-sm"
                       step="0.01"
                       min="0"
                       data-listing-compare-unit-filter-input>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1" for="listing-compare-unit-rent-max">Rent max</label>
                <input type="number"
                       id="listing-compare-unit-rent-max"
                       name="rent_max"
                       class="form-control form-control-sm"
                       step="0.01"
                       min="0"
                       data-listing-compare-unit-filter-input>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1" for="listing-compare-unit-price-min">Price min</label>
                <input type="number"
                       id="listing-compare-unit-price-min"
                       name="price_min"
                       class="form-control form-control-sm"
                       step="0.01"
                       min="0"
                       data-listing-compare-unit-filter-input>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1" for="listing-compare-unit-price-max">Price max</label>
                <input type="number"
                       id="listing-compare-unit-price-max"
                       name="price_max"
                       class="form-control form-control-sm"
                       step="0.01"
                       min="0"
                       data-listing-compare-unit-filter-input>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1" for="listing-compare-unit-ho">HO</label>
                <select id="listing-compare-unit-ho"
                        name="ho"
                        class="form-select form-select-sm"
                        data-listing-compare-unit-filter-input>
                    <option value="">All</option>
                    @foreach ($lookups[ListingLookupGroups::HANDOVER_CONDITION] ?? [] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1" for="listing-compare-unit-avl">Avl</label>
                <select id="listing-compare-unit-avl"
                        name="avl"
                        class="form-select form-select-sm"
                        data-listing-compare-unit-filter-input>
                    <option value="">All</option>
                    @foreach ($lookups[ListingLookupGroups::AVAILABILITY] ?? [] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1" for="listing-compare-unit-bds">Bds</label>
                <select id="listing-compare-unit-bds"
                        name="bds"
                        class="form-select form-select-sm"
                        data-listing-compare-unit-filter-input>
                    <option value="">All</option>
                    @foreach ($lookups[ListingLookupGroups::BEDROOMS] ?? [] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1" for="listing-compare-unit-type">Type</label>
                <select id="listing-compare-unit-type"
                        name="type"
                        class="form-select form-select-sm"
                        data-listing-compare-unit-filter-input>
                    <option value="">All</option>
                    @foreach ($lookups[ListingLookupGroups::PROPERTY_TYPE] ?? [] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1" for="listing-compare-unit-lease">Lease</label>
                <select id="listing-compare-unit-lease"
                        name="lease"
                        class="form-select form-select-sm"
                        data-listing-compare-unit-filter-input>
                    <option value="">All</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1" for="listing-compare-unit-sale">Sale</label>
                <select id="listing-compare-unit-sale"
                        name="sale"
                        class="form-select form-select-sm"
                        data-listing-compare-unit-filter-input>
                    <option value="">All</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
    <style>
        [data-listing-compare-unit-filter] .listings-filters-panel:not(.show) {
            display: none;
        }

        [data-listing-compare-unit-filter] .listings-filters-panel.show {
            display: block;
        }
    </style>
    @endpush
@endonce
