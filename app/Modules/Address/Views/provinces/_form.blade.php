<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
           value="{{ old('name', $province?->name) }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="code" class="form-label">Code</label>
    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code"
           value="{{ old('code', $province?->code) }}" placeholder="Optional short code">
    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-4 form-check">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror" id="is_active" name="is_active" value="1"
           {{ old('is_active', $province?->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">Active</label>
    @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
