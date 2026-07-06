@props([
    'actions',
    'record',
])

<div class="d-inline-flex gap-1 justify-content-end align-items-center flex-wrap">
    @include('propertylistings::listings._brochure-shortcuts', [
        'listing' => $record,
        'compact' => true,
    ])
    @include('propertylistings::listings._compare-toggle', [
        'listing' => $record,
        'class' => 'btn btn-sm btn-outline-secondary admin-icon-btn',
    ])
    <x-admin.list.row-actions :actions="$actions" :record="$record" />
</div>
