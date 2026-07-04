@php
    use App\Modules\PropertyListings\Support\ListingLookupGroups;

    $assets = old('assets', ($listing ?? null)?->assets?->loadMissing('media')->map(fn ($asset) => [
        'id' => $asset->id,
        'asset_type' => $asset->asset_type,
        'media_id' => $asset->media_id,
        'sort_order' => $asset->sort_order,
        'filename' => $asset->media?->filename ?? $asset->media?->original_filename ?? '',
        'preview_url' => $asset->media?->variantUrl('thumbnail') ?? $asset->media?->url(),
    ])->values()->all() ?? []);
@endphp

<div class="listing-assets-list">
    <div class="small fw-semibold mb-2">Assets List</div>
    <p class="small text-muted">Drag rows to reorder. Save Listing persists sort order. Remove rows to delete on save.</p>

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 admin-list-table table-striped table-hover table-borderless">
            <thead>
                <tr class="small text-muted">
                    <th style="width:2rem;"></th>
                    <th style="width:4rem;">Preview</th>
                    <th>Asset Type</th>
                    <th>Filename</th>
                    <th style="width:2.5rem;"></th>
                </tr>
            </thead>
            <tbody id="listing-assets-rows"
                   data-sortable-tbody
                   data-sortable-row="[data-listing-asset-row]">
                @forelse ($assets as $index => $asset)
                    <tr class="listing-asset-row" data-listing-asset-row draggable="true">
                        <td class="listing-drag-handle" style="cursor:grab;color:#64748b;" title="Drag to reorder">
                            <i class="{{ admin_icon('bi-grip-vertical') }} fs-5" aria-hidden="true"></i>
                        </td>
                        <td>
                            @if (! empty($asset['preview_url']))
                                <img src="{{ $asset['preview_url'] }}" alt="" class="rounded border" style="width:40px;height:40px;object-fit:cover;">
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="small">{{ $lookups[ListingLookupGroups::IMAGE_TYPE][$asset['asset_type'] ?? ''] ?? ($asset['asset_type'] ?? '—') }}</td>
                        <td class="small"><code>{{ $asset['filename'] ?: '—' }}</code></td>
                        <td class="text-end">
                            @if (! empty($asset['id']))
                                <input type="hidden" form="listing-edit-form" name="assets[{{ $index }}][id]" value="{{ $asset['id'] }}">
                            @endif
                            <input type="hidden" form="listing-edit-form" name="assets[{{ $index }}][asset_type]" value="{{ $asset['asset_type'] ?? '' }}">
                            <input type="hidden" form="listing-edit-form" name="assets[{{ $index }}][media_id]" value="{{ $asset['media_id'] ?? '' }}">
                            <input type="hidden" form="listing-edit-form" name="assets[{{ $index }}][sort_order]" value="{{ $asset['sort_order'] ?? 0 }}" data-sort-order>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-listing-asset-remove aria-label="Remove asset">
                                <i class="{{ admin_icon('bi-trash') }}" aria-hidden="true"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr data-assets-empty-row>
                        <td colspan="5" class="small text-muted py-3">No assets yet — use the upload pane above.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('propertylistings::listings._sortable-rows')

@once
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.getElementById('listing-assets-rows');

        const reindexRows = () => {
            const rows = tbody?.querySelectorAll('[data-listing-asset-row]') ?? [];
            rows.forEach((row, index) => {
                row.querySelectorAll('[form="listing-edit-form"]').forEach((input) => {
                    input.name = input.name.replace(/assets\[\d+\]/, `assets[${index}]`);
                });
            });
            window.listingSortableRefresh?.(tbody);
        };

        tbody?.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-listing-asset-remove]');
            if (!btn) {
                return;
            }

            btn.closest('[data-listing-asset-row]')?.remove();

            if (tbody.querySelectorAll('[data-listing-asset-row]').length === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.dataset.assetsEmptyRow = '';
                emptyRow.innerHTML = '<td colspan="5" class="small text-muted py-3">No assets yet — use the upload pane above.</td>';
                tbody.appendChild(emptyRow);
            }

            reindexRows();
        });
    });
    </script>
    @endpush
@endonce
