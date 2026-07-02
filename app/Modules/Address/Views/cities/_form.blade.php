<div class="mb-3">
    <label for="province_id" class="form-label">Province</label>
    <select class="form-select @error('province_id') is-invalid @enderror" id="province_id" name="province_id" required>
        <option value="">Select province...</option>
        @foreach ($provinces as $province)
            <option value="{{ $province->id }}" {{ old('province_id', $city?->province_id) == $province->id ? 'selected' : '' }}>
                {{ $province->name }}
            </option>
        @endforeach
    </select>
    @error('province_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
           value="{{ old('name', $city?->name) }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-4 form-check">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror" id="is_active" name="is_active" value="1"
           {{ old('is_active', $city?->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">Active</label>
    @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
