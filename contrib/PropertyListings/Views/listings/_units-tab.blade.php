@php
    use App\Modules\PropertyListings\Support\ListingLookupGroups;

    $units = old('units', ($listing ?? null)?->units?->map(fn ($unit) => [
        'id' => $unit->id,
        'floor' => $unit->floor,
        'unit' => $unit->unit,
        'area_size' => $unit->area_size,
        'rental' => $unit->rental,
        'handover_condition' => $unit->handover_condition,
        'availability' => $unit->availability,
        'bedrooms' => $unit->bedrooms,
        'selling_price' => $unit->selling_price,
        'property_type' => $unit->property_type,
        'for_lease' => $unit->for_lease,
        'for_sale' => $unit->for_sale,
        'last_remarks' => $unit->last_remarks,
        'sort_order' => $unit->sort_order,
    ])->values()->all() ?? []);

    if ($units === []) {
        $units = [['floor' => '', 'unit' => '', 'for_lease' => false, 'for_sale' => false, 'sort_order' => 0]];
    }
@endphp

<div class="listing-units-tab">
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-listing-units-add>
            <i class="{{ admin_icon('bi-plus-lg') }} me-1"></i> Add Unit
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 admin-list-table table-striped table-hover table-borderless listing-units-table">
            <colgroup>
                <col class="listing-unit-drag-col">
                <col class="listing-unit-floor-col">
                <col class="listing-unit-unit-col">
                <col class="listing-unit-area-col">
                <col class="listing-unit-rent-col">
                <col class="listing-unit-ho-col">
                <col class="listing-unit-avl-col">
                <col class="listing-unit-bds-col">
                <col class="listing-unit-price-col">
                <col class="listing-unit-type-col">
                <col class="listing-unit-lease-col">
                <col class="listing-unit-sale-col">
                <col class="listing-unit-action-col">
            </colgroup>
            <thead>
                <tr class="small text-muted">
                    <th></th>
                    <th title="Floor">Fl</th>
                    <th title="Unit">Unit</th>
                    <th title="Area Size" class="text-end">Area</th>
                    <th title="Rental" class="text-end">Rent</th>
                    <th title="Handover Condition">HO</th>
                    <th title="Availability">Avl</th>
                    <th title="Bedrooms">Bds</th>
                    <th title="Selling Price" class="text-end">Price</th>
                    <th title="Property Type">Type</th>
                    <th title="For Lease" class="text-center text-nowrap">For Lease</th>
                    <th title="For Sale" class="text-center text-nowrap">For Sale</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="listing-units-rows"
                   data-sortable-tbody
                   data-sortable-row="[data-listing-unit-row]">
                @foreach ($units as $index => $unit)
                    <tr class="listing-unit-row" data-listing-unit-row draggable="true">
                        <td class="listing-drag-handle" style="cursor:grab;color:#64748b;" title="Drag to reorder">
                            <i class="{{ admin_icon('bi-grip-vertical') }} fs-5" aria-hidden="true"></i>
                        </td>
                        <td class="listing-unit-floor-col">
                            @if (! empty($unit['id']))
                                <input type="hidden" name="units[{{ $index }}][id]" value="{{ $unit['id'] }}">
                            @endif
                            <input type="text" class="form-control form-control-sm listing-unit-floor-input" name="units[{{ $index }}][floor]" value="{{ $unit['floor'] ?? '' }}">
                        </td>
                        <td class="listing-unit-unit-col"><input type="text" class="form-control form-control-sm listing-unit-unit-input" name="units[{{ $index }}][unit]" value="{{ $unit['unit'] ?? '' }}"></td>
                        <td class="listing-unit-area-col"><input type="number" step="0.01" class="form-control form-control-sm text-end listing-unit-area-input" name="units[{{ $index }}][area_size]" value="{{ $unit['area_size'] ?? '' }}"></td>
                        <td class="listing-unit-rent-col"><input type="number" step="0.01" class="form-control form-control-sm text-end listing-unit-rent-input" name="units[{{ $index }}][rental]" value="{{ $unit['rental'] ?? '' }}"></td>
                        <td class="listing-unit-ho-col">
                            <select class="form-select form-select-sm listing-unit-select listing-unit-ho-select" name="units[{{ $index }}][handover_condition]">
                                <option value="">—</option>
                                @foreach ($lookups[ListingLookupGroups::HANDOVER_CONDITION] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(($unit['handover_condition'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="listing-unit-avl-col">
                            <select class="form-select form-select-sm listing-unit-select listing-unit-avl-select" name="units[{{ $index }}][availability]">
                                <option value="">—</option>
                                @foreach ($lookups[ListingLookupGroups::AVAILABILITY] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(($unit['availability'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="listing-unit-bds-col">
                            <select class="form-select form-select-sm listing-unit-select listing-unit-bds-select" name="units[{{ $index }}][bedrooms]">
                                <option value="">—</option>
                                @foreach ($lookups[ListingLookupGroups::BEDROOMS] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(($unit['bedrooms'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="listing-unit-price-col"><input type="number" step="0.01" class="form-control form-control-sm text-end listing-unit-price-input" name="units[{{ $index }}][selling_price]" value="{{ $unit['selling_price'] ?? '' }}"></td>
                        <td class="listing-unit-type-col">
                            <select class="form-select form-select-sm listing-unit-select listing-unit-type-select" name="units[{{ $index }}][property_type]">
                                <option value="">—</option>
                                @foreach ($lookups[ListingLookupGroups::PROPERTY_TYPE] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(($unit['property_type'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-center">
                            <input type="hidden" name="units[{{ $index }}][for_lease]" value="0">
                            <input type="checkbox" class="form-check-input" name="units[{{ $index }}][for_lease]" value="1" @checked(! empty($unit['for_lease']))>
                        </td>
                        <td class="text-center">
                            <input type="hidden" name="units[{{ $index }}][for_sale]" value="0">
                            <input type="checkbox" class="form-check-input" name="units[{{ $index }}][for_sale]" value="1" @checked(! empty($unit['for_sale']))>
                        </td>
                        <td class="text-end">
                            <input type="hidden" name="units[{{ $index }}][sort_order]" value="{{ $unit['sort_order'] ?? 0 }}" data-sort-order>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-listing-unit-remove aria-label="Remove unit">
                                <i class="{{ admin_icon('bi-trash') }}" aria-hidden="true"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<template id="listing-unit-row-template">
    <tr class="listing-unit-row" data-listing-unit-row draggable="true">
        <td class="listing-drag-handle" style="cursor:grab;color:#64748b;"><i class="{{ admin_icon('bi-grip-vertical') }} fs-5" aria-hidden="true"></i></td>
        <td class="listing-unit-floor-col"><input type="text" class="form-control form-control-sm listing-unit-floor-input" data-field="floor"></td>
        <td class="listing-unit-unit-col"><input type="text" class="form-control form-control-sm listing-unit-unit-input" data-field="unit"></td>
        <td class="listing-unit-area-col"><input type="number" step="0.01" class="form-control form-control-sm text-end listing-unit-area-input" data-field="area_size"></td>
        <td class="listing-unit-rent-col"><input type="number" step="0.01" class="form-control form-control-sm text-end listing-unit-rent-input" data-field="rental"></td>
        <td class="listing-unit-ho-col"><select class="form-select form-select-sm listing-unit-select listing-unit-ho-select" data-field="handover_condition"><option value="">—</option>@foreach ($lookups[ListingLookupGroups::HANDOVER_CONDITION] ?? [] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></td>
        <td class="listing-unit-avl-col"><select class="form-select form-select-sm listing-unit-select listing-unit-avl-select" data-field="availability"><option value="">—</option>@foreach ($lookups[ListingLookupGroups::AVAILABILITY] ?? [] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></td>
        <td class="listing-unit-bds-col"><select class="form-select form-select-sm listing-unit-select listing-unit-bds-select" data-field="bedrooms"><option value="">—</option>@foreach ($lookups[ListingLookupGroups::BEDROOMS] ?? [] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></td>
        <td class="listing-unit-price-col"><input type="number" step="0.01" class="form-control form-control-sm text-end listing-unit-price-input" data-field="selling_price"></td>
        <td class="listing-unit-type-col"><select class="form-select form-select-sm listing-unit-select listing-unit-type-select" data-field="property_type"><option value="">—</option>@foreach ($lookups[ListingLookupGroups::PROPERTY_TYPE] ?? [] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></td>
        <td class="text-center"><input type="hidden" data-field="for_lease_hidden" value="0"><input type="checkbox" class="form-check-input" data-field="for_lease" value="1"></td>
        <td class="text-center"><input type="hidden" data-field="for_sale_hidden" value="0"><input type="checkbox" class="form-check-input" data-field="for_sale" value="1"></td>
        <td class="text-end"><input type="hidden" data-field="sort_order" value="0" data-sort-order><button type="button" class="btn btn-sm btn-outline-danger" data-listing-unit-remove aria-label="Remove unit"><i class="{{ admin_icon('bi-trash') }}" aria-hidden="true"></i></button></td>
    </tr>
</template>

@include('propertylistings::listings._sortable-rows')

@push('styles')
<style>
    .listing-units-table {
        width: max-content;
        max-width: 100%;
        table-layout: fixed;
    }

    .listing-units-table col.listing-unit-drag-col { width: 2.75rem; }
    .listing-units-table col.listing-unit-floor-col { width: 4rem; }
    .listing-units-table col.listing-unit-unit-col { width: 5.25rem; }
    .listing-units-table col.listing-unit-area-col { width: 5.75rem; }
    .listing-units-table col.listing-unit-rent-col { width: 5.75rem; }
    .listing-units-table col.listing-unit-ho-col { width: 10.25rem; }
    .listing-units-table col.listing-unit-avl-col { width: 7.25rem; }
    .listing-units-table col.listing-unit-bds-col { width: 6.25rem; }
    .listing-units-table col.listing-unit-price-col { width: 7.25rem; }
    .listing-units-table col.listing-unit-type-col { width: 15.25rem; }
    .listing-units-table col.listing-unit-lease-col { width: 5.75rem; }
    .listing-units-table col.listing-unit-sale-col { width: 5.5rem; }
    .listing-units-table col.listing-unit-action-col { width: 3rem; }

    .listing-units-table th,
    .listing-units-table td {
        padding: 0.35rem 0.45rem;
        vertical-align: middle;
    }

    .listing-units-table td {
        overflow: hidden;
    }

    .listing-units-table .listing-drag-handle {
        padding-left: 0.35rem;
        padding-right: 0.25rem;
    }

    .listing-units-table .listing-unit-floor-input,
    .listing-units-table .listing-unit-unit-input,
    .listing-units-table .listing-unit-area-input,
    .listing-units-table .listing-unit-rent-input,
    .listing-units-table .listing-unit-price-input {
        display: block;
        flex: none !important;
        padding: 0.3rem 0.35rem;
        font-size: 0.875rem;
    }

    .listing-units-table .listing-unit-floor-input {
        width: 3.25rem !important;
        min-width: 3.25rem !important;
        max-width: 3.25rem !important;
    }

    .listing-units-table .listing-unit-unit-input {
        width: 4.5rem !important;
        min-width: 4.5rem !important;
        max-width: 4.5rem !important;
    }

    .listing-units-table .listing-unit-area-input,
    .listing-units-table .listing-unit-rent-input {
        width: 5rem !important;
        min-width: 5rem !important;
        max-width: 5rem !important;
        margin-left: auto;
    }

    .listing-units-table .listing-unit-price-input {
        width: 6.5rem !important;
        min-width: 6.5rem !important;
        max-width: 6.5rem !important;
        margin-left: auto;
    }

    .listing-units-table .listing-unit-select {
        display: block;
        flex: none !important;
        font-size: 0.875rem;
        line-height: 1.4;
        color: #0f172a;
        background-color: #fff;
        padding: 0.35rem 1.75rem 0.35rem 0.5rem;
        text-overflow: ellipsis;
    }

    .listing-units-table .listing-unit-ho-select {
        width: 10rem !important;
        min-width: 10rem !important;
        max-width: 10rem !important;
    }

    .listing-units-table .listing-unit-avl-select {
        width: 7rem !important;
        min-width: 7rem !important;
        max-width: 7rem !important;
    }

    .listing-units-table .listing-unit-bds-select {
        width: 6rem !important;
        min-width: 6rem !important;
        max-width: 6rem !important;
    }

    .listing-units-table .listing-unit-type-select {
        width: 15rem !important;
        min-width: 15rem !important;
        max-width: 15rem !important;
    }
</style>
@endpush

@once
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.getElementById('listing-units-rows');
        const template = document.getElementById('listing-unit-row-template');
        const addBtn = document.querySelector('[data-listing-units-add]');

        const reindexRows = () => {
            tbody?.querySelectorAll('[data-listing-unit-row]').forEach((row, index) => {
                row.querySelectorAll('[name]').forEach((input) => {
                    input.name = input.name.replace(/units\[\d+\]/, `units[${index}]`);
                });
                row.querySelectorAll('[data-field]').forEach((input) => {
                    const field = input.dataset.field;
                    if (field.endsWith('_hidden')) {
                        input.name = `units[${index}][${field.replace('_hidden', '')}]`;
                        return;
                    }
                    input.name = `units[${index}][${field}]`;
                });
            });
            window.listingSortableRefresh?.(tbody);
        };

        addBtn?.addEventListener('click', () => {
            if (!tbody || !template) return;
            const index = tbody.querySelectorAll('[data-listing-unit-row]').length;
            const row = template.content.firstElementChild.cloneNode(true);
            row.querySelectorAll('[data-field]').forEach((input) => {
                const field = input.dataset.field;
                if (field.endsWith('_hidden')) {
                    input.name = `units[${index}][${field.replace('_hidden', '')}]`;
                    return;
                }
                input.name = `units[${index}][${field}]`;
            });
            tbody.appendChild(row);
            reindexRows();
        });

        tbody?.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-listing-unit-remove]');
            if (!btn) return;
            btn.closest('[data-listing-unit-row]')?.remove();
            reindexRows();
        });
    });
    </script>
    @endpush
@endonce
