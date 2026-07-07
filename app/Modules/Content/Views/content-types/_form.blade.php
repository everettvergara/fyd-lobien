@php
    $isEdit = isset($contentType);
@endphp

@if (! $isEdit)
    <div class="mb-3">
        <label for="key" class="form-label">Key</label>
        <input type="text" class="form-control @error('key') is-invalid @enderror" id="key" name="key" value="{{ old('key') }}" required placeholder="article">
        <div class="form-text">Unique identifier used in URLs and the database. Lowercase letters, numbers, and hyphens only. Cannot be changed after creation.</div>
        @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
@else
    <div class="mb-3">
        <label class="form-label">Key</label>
        <div><code>{{ $contentType->key }}</code></div>
    </div>
@endif

<div class="mb-3">
    <label for="slug" class="form-label">Slug</label>
    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $contentType?->slug) }}" placeholder="articles">
    <div class="form-text">
        Optional public URL segment, separate from the key. When set, published entries list at <code>/{slug}</code> with pagination
        (e.g. <code>articles</code> → <code>/articles</code>). Leave blank to disable type listing (default for <code>page</code> and new types).
        The built-in <code>article</code> type defaults to <code>articles</code>.
    </div>
    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="label" class="form-label">Label</label>
    <input type="text" class="form-control @error('label') is-invalid @enderror" id="label" name="label" value="{{ old('label', $contentType?->label) }}" required>
    @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2">{{ old('description', $contentType?->description) }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="icon" class="form-label">Icon class</label>
    <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ old('icon', $contentType?->icon ?? 'bi-file-earmark') }}" required placeholder="bi-journal-text">
    <div class="form-text">Bootstrap Icons class, e.g. <code>bi-file-earmark-text</code>.</div>
    @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="sort_order" class="form-label">Sort order</label>
        <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $contentType?->sort_order ?? 0) }}">
        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label d-block">Status</label>
        <div class="form-check form-switch mt-2">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $contentType?->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
        @error('is_active')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>
