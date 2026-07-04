@php
    use App\Modules\PropertyListings\Support\ListingLookupGroups;

    $listingModel = $listing ?? null;
    $canUpload = ($listingModel?->exists ?? false) && auth()->user()?->can('batchAssets', App\Modules\PropertyListings\Models\Listing::class);
    $registry = app(App\Modules\PropertyListings\Support\ListingLookupRegistry::class);
    $imageTypes = $lookups[ListingLookupGroups::IMAGE_TYPE] ?? [];
@endphp

<div class="listing-assets-upload-pane card mb-3 border-start border-3 border-info" data-listing-assets-upload-pane>
    <div class="card-header py-2 small fw-semibold d-flex justify-content-between align-items-center">
        <span>Bulk Upload by Type</span>
        @if ($canUpload)
            <button type="button"
                    class="btn btn-sm btn-link p-0 text-decoration-none"
                    data-listing-assets-upload-toggle
                    aria-expanded="true"
                    aria-controls="listing-assets-upload-body">
                <span data-listing-assets-upload-toggle-label>Hide upload</span>
                <i class="{{ admin_icon('bi-chevron-up') }} ms-1" data-listing-assets-upload-toggle-icon aria-hidden="true"></i>
            </button>
        @endif
    </div>
    <div id="listing-assets-upload-body" class="card-body p-3" data-listing-assets-upload-body>
        @if (! $canUpload)
            <p class="small text-muted mb-0">Save the listing first to upload assets.</p>
        @else
            <p class="small text-muted">
                Choose one or more files for each asset type you need. Wrong extensions for the type will be rejected
                (e.g. flyers = PDF only). Uploading a type replaces any existing asset of that type.
            </p>

            <div class="table-responsive">
                <table class="table table-sm align-middle mb-3 listing-assets-upload-table">
                    <thead>
                        <tr class="small text-muted">
                            <th style="width:30%;">Asset Type</th>
                            <th style="width:20%;">Allowed ext</th>
                            <th style="min-width:20rem;">File</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($imageTypes as $value => $label)
                            @php
                                $allowed = $registry->allowedExtensions(ListingLookupGroups::IMAGE_TYPE, $value);
                                $accept = collect($allowed)->map(fn ($ext) => '.'.$ext)->implode(',');
                                $inputId = 'typed_file_'.$value;
                            @endphp
                            <tr>
                                <td class="small">{{ $label }}</td>
                                <td class="small"><code>{{ implode(', ', $allowed) ?: '—' }}</code></td>
                                <td>
                                    <div class="listing-asset-file-picker d-flex flex-wrap align-items-center gap-2">
                                        <input type="file"
                                               class="visually-hidden @error('typed_files.'.$value) is-invalid @enderror"
                                               id="{{ $inputId }}"
                                               form="listing-assets-upload-form"
                                               name="typed_files[{{ $value }}][]"
                                               data-listing-asset-file-input
                                               multiple
                                               @if ($accept !== '') accept="{{ $accept }}" @endif>
                                        <label for="{{ $inputId }}" class="btn btn-sm btn-outline-secondary mb-0">Choose files</label>
                                        <span class="small text-muted" data-listing-asset-file-name>No file chosen</span>
                                    </div>
                                    @error('typed_files.'.$value)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    @error('typed_files.'.$value.'.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @error('typed_files')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

            <div class="d-flex justify-content-end gap-2 listing-assets-upload-actions">
                <button type="submit"
                        form="listing-assets-upload-form"
                        class="btn btn-sm btn-primary"
                        data-listing-assets-upload-submit
                        disabled>
                    <i class="{{ admin_icon('bi-cloud-upload') }} me-1" aria-hidden="true"></i>
                    Upload Files
                </button>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .listing-assets-upload-pane .table-responsive {
        overflow-x: auto;
        overflow-y: visible;
    }

    .listing-assets-upload-pane td:last-child {
        min-width: 20rem;
        overflow: visible;
        vertical-align: middle;
    }

    .listing-assets-upload-pane.is-upload-pane-hidden [data-listing-assets-upload-body] {
        display: none;
    }

    .listing-assets-upload-pane.is-upload-pane-hidden [data-listing-assets-upload-toggle-icon] {
        transform: rotate(180deg);
    }

    .listing-assets-upload-actions {
        position: sticky;
        bottom: 0;
        padding-top: 0.5rem;
        background: linear-gradient(180deg, rgba(13, 202, 240, 0) 0%, rgba(13, 202, 240, 0.08) 35%);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const STORAGE_KEY = 'listings_assets_upload_open';
    const pane = document.querySelector('[data-listing-assets-upload-pane]');
    const toggleBtn = document.querySelector('[data-listing-assets-upload-toggle]');
    const toggleLabel = document.querySelector('[data-listing-assets-upload-toggle-label]');
    const fileInputs = document.querySelectorAll('[data-listing-asset-file-input]');
    const uploadSubmitBtn = document.querySelector('[data-listing-assets-upload-submit]');

    const setUploadOpen = (open) => {
        pane?.classList.toggle('is-upload-pane-hidden', !open);
        toggleBtn?.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (toggleLabel) {
            toggleLabel.textContent = open ? 'Hide upload' : 'Show upload';
        }
        sessionStorage.setItem(STORAGE_KEY, open ? '1' : '0');
    };

    const stored = sessionStorage.getItem(STORAGE_KEY);
    setUploadOpen(stored === null ? true : stored === '1');

    toggleBtn?.addEventListener('click', () => {
        const isOpen = !pane?.classList.contains('is-upload-pane-hidden');
        setUploadOpen(!isOpen);
    });

    const syncUploadSubmitState = () => {
        if (!uploadSubmitBtn) {
            return;
        }

        const hasFiles = Array.from(fileInputs).some((input) => (input.files?.length ?? 0) > 0);
        uploadSubmitBtn.disabled = !hasFiles;
    };

    fileInputs.forEach((input) => {
        input.addEventListener('change', () => {
            const nameEl = input.closest('.listing-asset-file-picker')?.querySelector('[data-listing-asset-file-name]');
            if (!nameEl) {
                syncUploadSubmitState();
                return;
            }

            const files = Array.from(input.files ?? []);
            if (files.length === 0) {
                nameEl.textContent = 'No file chosen';
            } else {
                nameEl.textContent = files.length === 1
                    ? files[0].name
                    : `${files.length} files selected`;
            }

            syncUploadSubmitState();
        });
    });

    syncUploadSubmitState();
});
</script>
@endpush
