@php
    use App\Models\Media;
    use App\Modules\PropertyListings\Support\ListingLookupGroups;

    $mapListingAsset = static fn ($asset) => [
        'id' => $asset->id,
        'asset_type' => $asset->asset_type,
        'media_id' => $asset->media_id,
        'sort_order' => $asset->sort_order,
        'filename' => $asset->media?->filename ?? $asset->media?->original_filename ?? '',
        'preview_url' => $asset->media?->variantUrl('thumbnail') ?? $asset->media?->url(),
        'full_url' => $asset->media?->url(),
        'is_pdf' => $asset->media?->isPdf() ?? false,
    ];

    $mapOldAsset = static function (array $asset): array {
        $media = ! empty($asset['media_id']) ? Media::query()->find($asset['media_id']) : null;

        return [
            'id' => $asset['id'] ?? null,
            'asset_type' => $asset['asset_type'] ?? '',
            'media_id' => $asset['media_id'] ?? null,
            'sort_order' => $asset['sort_order'] ?? 0,
            'filename' => $asset['filename'] ?? $media?->filename ?? $media?->original_filename ?? '',
            'preview_url' => $asset['preview_url'] ?? $media?->variantUrl('thumbnail') ?? $media?->url(),
            'full_url' => $asset['full_url'] ?? $media?->url(),
            'is_pdf' => $asset['is_pdf'] ?? $media?->isPdf() ?? false,
        ];
    };

    $oldAssets = old('assets');
    if (is_array($oldAssets) && $oldAssets !== []) {
        $assets = collect($oldAssets)
            ->filter(fn ($asset) => is_array($asset))
            ->map($mapOldAsset)
            ->values()
            ->all();
    } else {
        $assets = ($listing ?? null)?->assets?->loadMissing('media')->map($mapListingAsset)->values()->all() ?? [];
    }
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
                            @if (! empty($asset['is_pdf']) && ! empty($asset['full_url']))
                                <a href="{{ $asset['full_url'] }}"
                                   target="_blank"
                                   rel="noopener"
                                   class="d-inline-flex align-items-center justify-content-center rounded border text-danger text-decoration-none"
                                   style="width:40px;height:40px;"
                                   title="Open PDF"
                                   aria-label="Open {{ $asset['filename'] ?? 'PDF' }}">
                                    <i class="{{ admin_icon('bi-file-earmark-pdf') }} fs-4" aria-hidden="true"></i>
                                </a>
                            @elseif (! empty($asset['preview_url']) && ! empty($asset['full_url']))
                                <button type="button"
                                        class="btn p-0 border-0 d-block"
                                        data-listing-compare-preview
                                        data-preview-url="{{ $asset['full_url'] }}"
                                        data-preview-title="{{ $asset['filename'] ?? 'Asset preview' }}"
                                        aria-label="Preview {{ $asset['filename'] ?? 'asset' }}">
                                    <img src="{{ $asset['preview_url'] }}" alt="" class="rounded border" style="width:40px;height:40px;object-fit:cover;">
                                </button>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="small">{{ $lookups[ListingLookupGroups::IMAGE_TYPE][$asset['asset_type'] ?? ''] ?? ($asset['asset_type'] ?? '—') }}</td>
                        <td class="small"><code>{{ ($asset['filename'] ?? '') !== '' ? $asset['filename'] : '—' }}</code></td>
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
