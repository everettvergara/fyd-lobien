@props([
    'name',
    'label' => 'Image',
    'value' => null,
    'previewUrl' => null,
    'previewAlt' => null,
])

@php
    $selectedId = old($name, $value);
    $media = $selectedId ? \App\Models\Media::find($selectedId) : null;
    $imageUrl = $previewUrl ?? $media?->url();
    $imageAlt = $previewAlt ?? $media?->alt_text ?? $media?->original_filename;
@endphp

<div class="media-picker mb-3" data-picker-name="{{ $name }}">
    <label class="form-label">{{ $label }}</label>
    <input type="hidden" name="{{ $name }}" value="{{ $selectedId }}" class="media-picker-input">
    <div class="d-flex align-items-start gap-3">
        <div class="media-picker-preview border rounded bg-light d-flex align-items-center justify-content-center" style="width:120px;height:120px;overflow:hidden;">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="img-fluid media-picker-image">
            @else
                <span class="text-muted small px-2 text-center media-picker-placeholder">No image selected</span>
            @endif
        </div>
        <div class="d-flex flex-column gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary media-picker-open">Select Image</button>
            <button type="button" class="btn btn-sm btn-outline-secondary media-picker-clear {{ $selectedId ? '' : 'd-none' }}">Clear</button>
        </div>
    </div>
</div>
