<div class="card h-100 media-card" data-media-id="{{ $item->id }}">
    <div class="position-relative">
        <div class="position-absolute top-0 start-0 m-2">
            <input type="checkbox" class="form-check-input media-checkbox" name="ids[]" value="{{ $item->id }}">
        </div>
        <button type="button" class="btn p-0 w-100 media-preview-trigger" data-preview-url="{{ route('admin.media.preview', $item) }}">
            @if ($item->isImage())
                <img src="{{ $item->variantUrl('thumbnail') ?? $item->url() }}" class="card-img-top" alt="{{ $item->alt_text }}" style="height:38px;object-fit:cover;">
            @else
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:38px;">
                    @php
                        $placeholderIcon = match (true) {
                            $item->isVideo() => 'bi-film',
                            $item->isAudio() => 'bi-file-music',
                            $item->isPdf() => 'bi-file-pdf',
                            default => 'bi-file-earmark',
                        };
                    @endphp
                    <i class="{{ admin_icon($placeholderIcon) }} fs-1 text-muted"></i>
                </div>
            @endif
        </button>
    </div>
    <div class="card-body p-2">
        <p class="small mb-1 text-truncate" title="{{ $item->displayName() }}">{{ $item->displayName() }}</p>
        <p class="text-muted mb-2" style="font-size:0.75rem;">{{ $item->mime_type }} · {{ number_format($item->size / 1024, 1) }} KB</p>
        @include('media::partials.asset-actions', ['item' => $item])
    </div>
</div>
