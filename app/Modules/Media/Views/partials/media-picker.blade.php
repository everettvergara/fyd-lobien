@props([
    'name',
    'label' => 'Image',
    'value' => null,
    'values' => null,
    'mode' => 'single',
    'type' => 'image',
    'previewUrl' => null,
    'previewAlt' => null,
    'oldKey' => null,
])

@php
    $isMulti = $mode === 'multi';
    $isPdf = $type === 'pdf';

    if ($isMulti) {
        $selectedIds = old($oldKey ?? $name, $values ?? []);
        if (! is_array($selectedIds)) {
            $selectedIds = array_values(array_filter([$selectedIds]));
        }
        $mediaById = $selectedIds !== []
            ? \App\Models\Media::whereIn('id', $selectedIds)->get()->keyBy('id')
            : collect();
        $selectedItems = collect($selectedIds)
            ->map(fn ($id) => $mediaById->get($id))
            ->filter();
    } else {
        $selectedId = old($oldKey ?? $name, $value);
        $media = $selectedId ? \App\Models\Media::find($selectedId) : null;
        $imageUrl = $previewUrl ?? ($media && $media->isImage() ? $media->url() : null);
        $imageAlt = $previewAlt ?? $media?->alt_text ?? $media?->original_filename;
        $fileName = $media?->displayName();
    }
@endphp

<div class="media-picker mb-3" data-picker-name="{{ $name }}" data-picker-mode="{{ $mode }}" data-picker-type="{{ $type }}">
    @if ($label)
        <label class="form-label">{{ $label }}</label>
    @endif

    @if ($isMulti)
        <div class="media-picker-grid d-flex flex-wrap gap-2 mb-2">
            @foreach ($selectedItems as $item)
                <div class="media-picker-item position-relative border rounded bg-light" style="width:80px;height:80px;overflow:hidden;">
                    <img src="{{ $item->url() }}" alt="{{ $item->alt_text ?? $item->original_filename }}" class="img-fluid" style="width:100%;height:100%;object-fit:cover;">
                    <button type="button" class="btn btn-danger media-picker-remove-item position-absolute top-0 end-0" style="line-height:1;padding:0 4px;" aria-label="Remove image">&times;</button>
                    <input type="hidden" name="{{ $name }}[]" value="{{ $item->id }}" class="media-picker-input">
                </div>
            @endforeach
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-primary media-picker-open">Add Images</button>
            <button type="button" class="btn btn-outline-secondary media-picker-clear {{ $selectedItems->isNotEmpty() ? '' : 'd-none' }}">Clear All</button>
        </div>
    @else
        <input type="hidden" name="{{ $name }}" value="{{ $selectedId }}" class="media-picker-input">
        <div class="d-flex align-items-start gap-3">
            <div class="media-picker-preview border rounded bg-light d-flex align-items-center justify-content-center" style="width:120px;height:120px;overflow:hidden;">
                @if ($isPdf && $media?->isPdf())
                    <span class="text-muted small px-2 text-center media-picker-file">
                        <i class="bi bi-file-earmark-pdf fs-2 d-block mb-1" aria-hidden="true"></i>
                        {{ $fileName }}
                    </span>
                @elseif ($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="img-fluid media-picker-image">
                @else
                    <span class="text-muted small px-2 text-center media-picker-placeholder">{{ $isPdf ? 'No PDF selected' : 'No image selected' }}</span>
                @endif
            </div>
            <div class="d-flex flex-column gap-2">
                <button type="button" class="btn btn-outline-primary media-picker-open">{{ $isPdf ? 'Select PDF' : 'Select Image' }}</button>
                <button type="button" class="btn btn-outline-secondary media-picker-clear {{ $selectedId ? '' : 'd-none' }}">Clear</button>
            </div>
        </div>
    @endif
</div>
