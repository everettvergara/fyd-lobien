<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
           value="{{ old('name', $webform?->name) }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="slug" class="form-label">Slug</label>
    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug"
           value="{{ old('slug', $webform?->slug) }}" {{ $webform ? 'required' : '' }}
           placeholder="Auto-generated from name if left blank">
    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text">A public page is automatically created at <code>/{{ $webform?->slug ?: '{slug}' }}</code> when the form is active.</div>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $webform?->description) }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-check mb-3">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror" id="is_active" name="is_active" value="1"
           @checked(old('is_active', $webform?->is_active ?? true))>
    <label class="form-check-label" for="is_active">Active</label>
    @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
