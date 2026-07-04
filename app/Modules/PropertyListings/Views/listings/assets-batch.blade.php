@extends('admin.layouts.app')

@section('title', 'Batch Upload Assets')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Listings', 'url' => route('admin.listings.index')],
        ['label' => 'Batch Assets'],
    ]" />

    <x-admin.page-header
        title="Batch Upload Assets"
        :back-route="route('admin.listings.index')"
        back-label="Back to Listings"
    />

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-2 small">Upload Files</div>
                <div class="card-body">
                    <p class="small text-muted">
                        Upload multiple files or a ZIP archive. Filenames must match
                        <code>{code}__{asset_type_slug}.{ext}</code>. Existing assets with the same listing code and
                        asset type are replaced.
                    </p>

                    <form method="POST"
                          action="{{ route('admin.listings.assets.batch.preview') }}"
                          enctype="multipart/form-data">
                        @csrf
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
                                <ul class="mb-0 ps-3">
                                    @foreach ($preview['errors'] as $error)
                                        <li>{{ $error }}</li>
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
                                                    <span class="badge bg-success-subtle text-success">Ready</span>
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
                    @if (! empty($batchKey))
                        <div class="card-footer bg-white border-top">
                            <form method="POST" action="{{ route('admin.listings.assets.batch.commit') }}" class="d-flex gap-2">
                                @csrf
                                <input type="hidden" name="batch_key" value="{{ $batchKey }}">
                                <button type="submit" class="btn btn-primary">Confirm Upload</button>
                                <a href="{{ route('admin.listings.assets.batch') }}" class="btn btn-outline-secondary">Cancel</a>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
