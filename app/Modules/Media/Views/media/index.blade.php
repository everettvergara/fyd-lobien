@extends('admin.layouts.app')

@section('title', 'Media Library')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Media'],
    ]" />

    <x-admin.page-header title="Media Library">
        <x-slot:actions>
            @can('create', App\Models\Media::class)
                <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#mediaUploadPanel">
                    <i class="{{ admin_icon('bi-cloud-arrow-up') }} me-1"></i> Upload
                </button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    @can('create', App\Models\Media::class)
        <x-admin.card class="mb-4 collapse" id="mediaUploadPanel">
            <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" id="mediaUploadForm">
                @csrf
                <div class="border rounded bg-light p-4 text-center mb-3" id="mediaDropZone">
                    <i class="{{ admin_icon('bi-cloud-arrow-up') }} fs-1 text-muted"></i>
                    <p class="mb-2">Drag files here or choose files to upload.</p>
                    <input type="file" class="form-control @error('file') is-invalid @enderror @error('files') is-invalid @enderror @error('files.*') is-invalid @enderror" name="files[]" multiple>
                    @error('file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('files')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('files.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @foreach ($errors->get('files.*') as $messages)
                        @foreach ($messages as $message)
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @endforeach
                    @endforeach
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Folder</label>
                        <select name="folder_id" class="form-select">
                            <option value="">Library Root</option>
                            @foreach ($folders as $folder)
                                <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tags</label>
                        <input type="text" name="tags" class="form-control" placeholder="Comma-separated tags">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Alt Text</label>
                        <input type="text" name="alt_text" class="form-control">
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="small text-muted">Large files are validated and reported gracefully. The library refreshes after upload.</div>
                    <button type="submit" class="btn btn-primary">Start Upload</button>
                </div>
                <div class="mt-3 d-none" id="mediaUploadQueue"></div>
            </form>
        </x-admin.card>
    @endcan

    <x-admin.card class="mb-4">
        <form method="GET" class="d-flex flex-wrap gap-3 align-items-end p-3" data-admin-list-toolbar>
            <div class="admin-list-filter-field">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Filename, title, tags, MIME type...">
            </div>
            <div class="admin-list-filter-field">
                <label class="form-label">Type</label>
                <select class="form-select" name="type">
                    <option value="">All</option>
                    @foreach (['image' => 'Images', 'video' => 'Videos', 'audio' => 'Audio', 'pdf' => 'PDF', 'application' => 'Documents'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-list-filter-field">
                <label class="form-label">Folder</label>
                <select class="form-select" name="folder">
                    <option value="">All Folders</option>
                    @foreach ($folders as $folder)
                        <option value="{{ $folder->id }}" @selected(($filters['folder'] ?? '') == $folder->id)>{{ $folder->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-list-filter-field">
                <label class="form-label">Sort</label>
                <select class="form-select" name="sort">
                    @foreach (['created_at' => 'Upload Date', 'name' => 'Name', 'modified_at' => 'Modified Date', 'size' => 'Size', 'file_type' => 'File Type'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'created_at') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-list-filter-field">
                <label class="form-label">Order</label>
                <select class="form-select" name="direction">
                    <option value="desc" @selected(($filters['direction'] ?? 'desc') === 'desc')>Desc</option>
                    <option value="asc" @selected(($filters['direction'] ?? '') === 'asc')>Asc</option>
                </select>
            </div>
            <div class="admin-list-filter-actions d-flex gap-2">
                <button class="btn btn-outline-secondary">Apply</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.media.index') }}">Reset</a>
            </div>
        </form>
    </x-admin.card>

    @can('manageFolders', App\Models\Media::class)
        <x-admin.card class="mb-3" :padding="false">
            <form method="POST" action="{{ route('admin.media.folders.store') }}" class="d-flex flex-wrap gap-2 align-items-end p-2 admin-list-toolbar" data-admin-list-toolbar data-media-folder-form>
                @csrf
                <div class="admin-list-filter-field">
                    <label class="form-label" for="media-folder-name">Folder</label>
                    <input type="text" class="form-control" id="media-folder-name" name="name" placeholder="New folder">
                </div>
                <div class="admin-list-filter-field">
                    <label class="form-label" for="media-folder-parent">Parent</label>
                    <select name="parent_id" id="media-folder-parent" class="form-select">
                        <option value="">Library Root</option>
                        @foreach ($folders as $folder)
                            <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-list-filter-actions d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary">Create</button>
                </div>
            </form>
        </x-admin.card>
    @endcan

    <form method="POST" action="{{ route('admin.media.bulk') }}" id="mediaBulkForm">
        @csrf
        <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center mb-3">
            <div class="btn-group btn-group-sm" role="group" aria-label="View mode">
                @foreach (['grid' => 'Grid', 'list' => 'List'] as $mode => $label)
                    <a href="{{ request()->fullUrlWithQuery(['view' => $mode]) }}" class="btn btn-outline-secondary {{ $viewMode === $mode ? 'active' : '' }}" data-media-view="{{ $mode }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div class="p-3 admin-list-toolbar mb-3">
            <div class="d-flex flex-wrap gap-2 align-items-center media-bulk-bar" data-admin-list-bulk-form>
                <select name="action" class="form-select">
                    <option value="">Bulk Action</option>
                    <option value="archive">Archive</option>
                    <option value="restore">Restore</option>
                    <option value="delete">Delete</option>
                    <option value="zip">Download ZIP</option>
                    <option value="copy">Copy</option>
                    <option value="move">Move / Change Folder</option>
                    <option value="add_tags">Add Tags</option>
                    <option value="remove_tags">Remove Tags</option>
                </select>
                <select name="folder_id" class="form-select">
                    <option value="">Target Folder</option>
                    @foreach ($folders as $folder)
                        <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                    @endforeach
                </select>
                <input name="tags" class="form-control" placeholder="Tags">
                <button class="btn btn-outline-secondary">Apply</button>
            </div>
        </div>

        @if ($viewMode === 'list')
            <x-admin.card :padding="false">
                <div class="table-responsive">
                    <table class="table table-borderless table-striped table-hover align-middle mb-0 admin-list-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="form-check-input media-select-all"></th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                                <th>Usage</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($media as $item)
                                <tr>
                                    <td><input type="checkbox" class="form-check-input media-checkbox" name="ids[]" value="{{ $item->id }}"></td>
                                    <td>{{ $item->displayName() }}<div class="text-muted small">{{ $item->original_filename }}</div></td>
                                    <td>{{ $item->mime_type }}</td>
                                    <td>{{ number_format($item->size / 1024, 1) }} KB</td>
                                    <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $item->usages_count }}</td>
                                    <td class="text-end">@include('media::partials.asset-actions', ['item' => $item])</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-5">No media assets found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        @else
            <div class="row row-cols-xl-8 row-cols-md-6 row-cols-sm-4 g-3">
                @forelse ($media as $item)
                    <div class="col">
                        @include('media::partials.media-card', ['item' => $item])
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center text-muted py-5">No media assets found. Upload your first asset or adjust the filters.</div>
                    </div>
                @endforelse
            </div>
        @endif
    </form>

    @if ($media->hasPages())
        <div class="mt-3">{{ $media->links() }}</div>
    @endif

    @include('media::partials.preview-modal')
@endsection
