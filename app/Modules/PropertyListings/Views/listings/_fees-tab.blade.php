@php
    use App\Modules\PropertyListings\Support\ListingLookupGroups;

    $fees = old('fees', ($listing ?? null)?->fees?->map(fn ($fee) => [
        'id' => $fee->id,
        'fee_type' => $fee->fee_type,
        'fee' => $fee->fee,
        'sort_order' => $fee->sort_order,
    ])->values()->all() ?? []);

    if ($fees === []) {
        $fees = [['fee_type' => '', 'fee' => '', 'sort_order' => 0]];
    }
@endphp

<div class="listing-fees-tab">
    <div class="d-flex justify-content-end mb-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-listing-fees-add>
            <i class="{{ admin_icon('bi-plus-lg') }} me-1"></i> Add Fee
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 admin-list-table table-striped table-hover table-borderless listing-fees-table">
            <colgroup>
                <col class="listing-fee-drag-col">
                <col class="listing-fee-type-col">
                <col class="listing-fee-amount-col">
                <col class="listing-fee-action-col">
            </colgroup>
            <thead>
                <tr class="small text-muted">
                    @include('propertylistings::listings._drag-handle', ['header' => true])
                    <th>Fee Type</th>
                    <th>Fee Amount</th>
                    <th> </th>
                </tr>
            </thead>
            <tbody id="listing-fees-rows"
                   data-sortable-tbody
                   data-sortable-row="[data-listing-fee-row]">
                @foreach ($fees as $index => $fee)
                    <tr class="listing-fee-row" data-listing-fee-row draggable="true">
                        @include('propertylistings::listings._drag-handle')
                        <td class="listing-fee-type-col">
                            @if (! empty($fee['id']))
                                <input type="hidden" name="fees[{{ $index }}][id]" value="{{ $fee['id'] }}">
                            @endif
                            <select class="form-select listing-fee-type-select @error('fees.'.$index.'.fee_type') is-invalid @enderror"
                                    name="fees[{{ $index }}][fee_type]">
                                <option value="">—</option>
                                @foreach ($lookups[ListingLookupGroups::FEE_TYPE] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(($fee['fee_type'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('fees.'.$index.'.fee_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </td>
                        <td class="listing-fee-amount-col">
                            <input type="number"
                                   step="0.01"
                                   inputmode="decimal"
                                   class="form-control form-control-sm text-end listing-fee-amount-input @error('fees.'.$index.'.fee') is-invalid @enderror"
                                   name="fees[{{ $index }}][fee]"
                                   value="{{ $fee['fee'] ?? '' }}">
                            @error('fees.'.$index.'.fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </td>
                        <td class="text-end">
                            <input type="hidden" name="fees[{{ $index }}][sort_order]" value="{{ $fee['sort_order'] ?? 0 }}" data-sort-order>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-listing-fee-remove aria-label="Remove fee">
                                <i class="{{ admin_icon('bi-trash') }}" aria-hidden="true"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<template id="listing-fee-row-template">
    <tr class="listing-fee-row" data-listing-fee-row draggable="true">
        <td class="listing-drag-handle" style="cursor:grab;color:#64748b;"><i class="{{ admin_icon('bi-grip-vertical') }} fs-5" aria-hidden="true"></i></td>
        <td class="listing-fee-type-col">
            <select class="form-select listing-fee-type-select" data-field="fee_type">
                <option value="">—</option>
                @foreach ($lookups[ListingLookupGroups::FEE_TYPE] ?? [] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </td>
        <td class="listing-fee-amount-col"><input type="number" step="0.01" inputmode="decimal" class="form-control form-control-sm text-end listing-fee-amount-input" data-field="fee"></td>
        <td class="text-end"><input type="hidden" data-field="sort_order" value="0" data-sort-order><button type="button" class="btn btn-sm btn-outline-danger" data-listing-fee-remove aria-label="Remove fee"><i class="{{ admin_icon('bi-trash') }}" aria-hidden="true"></i></button></td>
    </tr>
</template>

@include('propertylistings::listings._sortable-rows')

@push('styles')
<style>
    .listing-fees-table {
        width: max-content;
        max-width: 100%;
        table-layout: fixed;
    }

    .listing-fees-table col.listing-fee-drag-col {
        width: 2rem;
    }

    .listing-fees-table col.listing-fee-type-col {
        width: 11rem;
    }

    .listing-fees-table col.listing-fee-amount-col {
        width: 4rem;
    }

    .listing-fees-table col.listing-fee-action-col {
        width: 2.25rem;
    }

    .listing-fees-table td {
        padding: 0.25rem 0.35rem;
        vertical-align: middle;
    }

    .listing-fees-table .listing-fee-type-col {
        overflow: hidden;
    }

    .listing-fees-table .listing-fee-type-select {
        display: block;
        width: 11rem !important;
        min-width: 11rem !important;
        max-width: 11rem !important;
        flex: none !important;
        font-size: 0.875rem;
        line-height: 1.4;
        color: #0f172a;
        background-color: #fff;
        padding-right: 1.75rem;
        text-overflow: ellipsis;
    }

    .listing-fees-table .invalid-feedback {
        font-size: 0.7rem;
        margin-top: 0.15rem;
    }

    .listing-fees-table .listing-fee-amount-input {
        display: block;
        width: 4rem !important;
        min-width: 4rem !important;
        max-width: 4rem !important;
        flex: none !important;
        padding-left: 0.35rem;
        padding-right: 0.35rem;
    }

    .listing-fees-tab .d-flex {
        margin-bottom: 0.5rem !important;
    }
</style>
@endpush

@once
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.getElementById('listing-fees-rows');
        const template = document.getElementById('listing-fee-row-template');
        const addBtn = document.querySelector('[data-listing-fees-add]');

        const reindexRows = () => {
            tbody?.querySelectorAll('[data-listing-fee-row]').forEach((row, index) => {
                row.querySelectorAll('[name]').forEach((input) => {
                    input.name = input.name.replace(/fees\[\d+\]/, `fees[${index}]`);
                });
                row.querySelectorAll('[data-field]').forEach((input) => {
                    input.name = `fees[${index}][${input.dataset.field}]`;
                });
            });
            window.listingSortableRefresh?.(tbody);
        };

        addBtn?.addEventListener('click', () => {
            if (!tbody || !template) return;
            const index = tbody.querySelectorAll('[data-listing-fee-row]').length;
            const row = template.content.firstElementChild.cloneNode(true);
            row.querySelectorAll('[data-field]').forEach((input) => {
                input.name = `fees[${index}][${input.dataset.field}]`;
            });
            tbody.appendChild(row);
            reindexRows();
        });

        tbody?.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-listing-fee-remove]');
            if (!btn) return;
            btn.closest('[data-listing-fee-row]')?.remove();
            reindexRows();
        });
    });
    </script>
    @endpush
@endonce
