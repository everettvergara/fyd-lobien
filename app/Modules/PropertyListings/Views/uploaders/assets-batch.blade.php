@extends('admin.layouts.app')

@php
    $registry = app(\App\Modules\PropertyListings\Support\ListingLookupRegistry::class);
    $assetTypes = $registry->options(\App\Modules\PropertyListings\Support\ListingLookupGroups::IMAGE_TYPE);
    $selectedAssetType = old('asset_type', $assetType ?? '');
@endphp

@section('title', 'Assets Uploader')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Property Uploaders', 'url' => route('admin.property-uploaders.index')],
        ['label' => 'Assets Uploader'],
    ]" />

    <x-admin.page-header
        title="Assets Uploader"
        :back-route="route('admin.property-uploaders.index')"
        back-label="Back to Uploaders"
    />

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-2 small">Upload Files</div>
                <div class="card-body">
                    <p class="small text-muted">
                        Choose one asset type for the batch, then upload multiple files or a ZIP archive.
                        Filenames must match <code>{code}__{whatever_text}.{ext}</code>. Existing assets with the
                        same listing code and selected asset type are replaced.
                    </p>

                    <form method="POST"
                          action="{{ route('admin.property-uploaders.assets.preview') }}"
                          enctype="multipart/form-data"
                          data-asset-sequential-upload
                          data-stage-start-url="{{ route('admin.property-uploaders.assets.stage.start') }}"
                          data-stage-file-url="{{ route('admin.property-uploaders.assets.stage.file') }}"
                          data-stage-validate-url="{{ route('admin.property-uploaders.assets.stage.validate') }}"
                          data-stage-preview-url="{{ route('admin.property-uploaders.assets.stage.preview') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="asset_type" class="form-label">Asset Type</label>
                            <select id="asset_type"
                                    name="asset_type"
                                    class="form-select @error('asset_type') is-invalid @enderror"
                                    required>
                                <option value="">Choose asset type</option>
                                @foreach ($assetTypes as $value => $label)
                                    <option value="{{ $value }}" @selected($selectedAssetType === $value)>{{ $label }} ({{ $value }})</option>
                                @endforeach
                            </select>
                            @error('asset_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="asset_files" class="form-label">Files or ZIP</label>
                            <input type="file"
                                   class="form-control @error('files') is-invalid @enderror"
                                   id="asset_files"
                                   name="files[]"
                                   accept=".zip,.jpg,.jpeg,.png,.gif,.webp,.bmp,.svg,.pdf"
                                   multiple
                                   required>
                            @error('files')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @error('files.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="asset-upload-queue d-none mb-3" data-asset-upload-queue>
                            <div class="small fw-semibold mb-2">Upload queue</div>
                            <div class="list-group small" data-asset-upload-queue-list></div>
                            <button type="button"
                                    class="btn btn-outline-primary btn-sm mt-3 d-none"
                                    data-asset-upload-preview-staged>
                                Preview staged files
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary">Preview Batch</button>
                    </form>
                </div>
            </div>
        </div>

        @if (! empty($preview))
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header py-2 small d-flex justify-content-between align-items-center">
                        <span>Preview</span>
                        <span class="badge bg-secondary-subtle text-secondary">{{ count($preview['items'] ?? []) }} files</span>
                    </div>
                    <div class="card-body p-0">
                        @if (! empty($preview['errors']))
                            <div class="alert alert-warning m-3 small">
                                <p class="fw-semibold mb-2">Fix these issues before uploading:</p>
                                <ul class="mb-0 ps-3">
                                    @foreach ($preview['errors'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (! empty($preview['warnings']))
                            <div class="alert alert-info m-3 small">
                                <p class="fw-semibold mb-2">These files will be skipped:</p>
                                <ul class="mb-0 ps-3">
                                    @foreach ($preview['warnings'] as $warning)
                                        <li>{{ $warning }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th class="small">Filename</th>
                                        <th class="small">Listing Code</th>
                                        <th class="small">Asset Type</th>
                                        <th class="small">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($preview['items'] ?? [] as $item)
                                        <tr>
                                            <td class="small"><code>{{ $item['filename'] ?? '' }}</code></td>
                                            <td class="small">{{ $item['code'] ?? '—' }}</td>
                                            <td class="small">{{ $item['asset_type'] ?? '—' }}</td>
                                            <td class="small">
                                                @if (! empty($item['valid']))
                                                    @if (! empty($item['replaces_existing']))
                                                        <span class="badge bg-info-subtle text-info">Replace</span>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success">Attach</span>
                                                    @endif
                                                @elseif (! empty($item['skipped']))
                                                    <span class="badge bg-warning-subtle text-warning">{{ $item['skip_reason'] ?? 'Skip' }}</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">{{ implode(' ', $item['errors'] ?? ['Invalid']) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if (! empty($batchKey) && empty($preview['errors']) && (($preview['summary']['valid'] ?? 0) > 0))
                        <div class="card-footer bg-white border-top">
                            <form method="POST"
                                  action="{{ route('admin.property-uploaders.assets.commit') }}"
                                  class="d-flex flex-column gap-2"
                                  @if (! empty($progressCommit))
                                      data-asset-confirm-upload
                                      data-progress-url="{{ route('admin.property-uploaders.assets.commit.progress') }}"
                                  @endif>
                                @csrf
                                <input type="hidden" name="batch_key" value="{{ $batchKey }}">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Confirm Upload</button>
                                    <a href="{{ route('admin.property-uploaders.assets') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                                <div class="d-none" data-asset-confirm-progress>
                                    <div class="progress" role="progressbar" aria-label="Upload progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                        <div class="progress-bar" style="width: 0%" data-asset-confirm-progress-bar>0%</div>
                                    </div>
                                    <div class="small text-muted mt-2" data-asset-confirm-progress-status>Ready.</div>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
