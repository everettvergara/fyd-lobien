<div class="d-flex flex-wrap gap-1 justify-content-end">
    <button type="button" class="btn admin-icon-btn media-preview-trigger" data-preview-url="{{ route('admin.media.preview', $item) }}" title="Preview" aria-label="Preview">
        <i class="{{ admin_icon('bi-eye') }}"></i>
    </button>

    @can('download', $item)
        <a href="{{ route('admin.media.download', $item) }}" class="btn admin-icon-btn" title="Download" aria-label="Download">
            <i class="{{ admin_icon('bi-download') }}"></i>
        </a>
    @endcan

    @can('update', $item)
        <button type="button" class="btn admin-icon-btn media-edit-trigger"
            title="Edit metadata"
            aria-label="Edit metadata"
            data-id="{{ $item->id }}"
            data-update-url="{{ route('admin.media.update', $item) }}"
            data-title="{{ $item->title }}"
            data-description="{{ $item->description }}"
            data-caption="{{ $item->caption }}"
            data-alt="{{ $item->alt_text }}"
            data-tags="{{ $item->tags->pluck('name')->implode(', ') }}">
            <i class="{{ admin_icon('bi-pencil') }}"></i>
        </button>

        <form method="POST" action="{{ route('admin.media.archive', $item) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn admin-icon-btn" title="Archive" aria-label="Archive">
                <i class="{{ admin_icon('bi-archive') }}"></i>
            </button>
        </form>
    @endcan

    @can('replace', $item)
        <button type="button" class="btn admin-icon-btn" title="Replace file" aria-label="Replace file" onclick="document.getElementById('replace-media-{{ $item->id }}').click()">
            <i class="{{ admin_icon('bi-arrow-repeat') }}"></i>
        </button>
        <form method="POST" action="{{ route('admin.media.replace', $item) }}" enctype="multipart/form-data" class="visually-hidden">
            @csrf
            <input id="replace-media-{{ $item->id }}" type="file" name="file" onchange="this.form.submit()">
        </form>
    @endcan

    @can('create', App\Models\Media::class)
        <form method="POST" action="{{ route('admin.media.duplicate', $item) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn admin-icon-btn" title="Duplicate" aria-label="Duplicate">
                <i class="{{ admin_icon('bi-files') }}"></i>
            </button>
        </form>
    @endcan

    @can('delete', $item)
        <form method="POST" action="{{ route('admin.media.destroy', $item) }}" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn admin-icon-btn admin-icon-btn-danger" title="Delete" aria-label="Delete" onclick="return confirm('Delete this asset? Assets in use require override from bulk actions or API.')">
                <i class="{{ admin_icon('bi-trash') }}"></i>
            </button>
        </form>
    @endcan
</div>
