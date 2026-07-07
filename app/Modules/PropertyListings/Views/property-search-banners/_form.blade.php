<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
           value="{{ old('name', $banner?->name) }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="key" class="form-label">Key</label>
    <input type="text" class="form-control @error('key') is-invalid @enderror font-monospace" id="key" name="key"
           value="{{ old('key', $banner?->key) }}" required
           pattern="[a-z0-9\-]+" title="Lowercase letters, numbers, and hyphens only">
    <div class="form-text">Used when attaching this banner to pages in Page Manager.</div>
    @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="heading" class="form-label">Heading</label>
    <input type="text" class="form-control @error('heading') is-invalid @enderror" id="heading" name="heading"
           value="{{ old('heading', $banner?->heading) }}" placeholder="Find your property">
    @error('heading')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@include('media::partials.media-picker', [
    'name' => 'background_image_id',
    'label' => 'Background Image',
    'value' => old('background_image_id', $banner?->background_image_id),
    'previewUrl' => $banner?->backgroundImage?->url(),
])

<div class="form-check mb-3">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror"
           id="is_active" name="is_active" value="1"
           @checked(old('is_active', $banner?->is_active ?? true))>
    <label class="form-check-label" for="is_active">Active</label>
    @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
