@php
    use App\Modules\PropertyListings\Support\ListingLookupGroups;
    $lookupModel = $lookup ?? null;
    $groupKey = old('group', $lookupModel?->group ?? $group ?? '');
    $showFileKind = ListingLookupGroups::usesFileKind($groupKey);
    $showPropertyTypeProfile = ListingLookupGroups::usesPropertyTypeProfile($groupKey);
@endphp

@if ($errors->any())
    <div class="alert alert-danger small">
        Please review the highlighted fields and try again.
    </div>
@endif

<input type="hidden" name="group" value="{{ $groupKey }}">

<div class="row g-3">
    <div class="col-md-6">
        <label for="lookup_value" class="form-label">Value</label>
        <input type="text"
               class="form-control @error('value') is-invalid @enderror"
               id="lookup_value"
               name="value"
               value="{{ old('value', $lookupModel?->value) }}"
               required
               pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
               @disabled($lookupModel !== null)>
        <div class="form-text">Stable key, e.g. <code>floor-plan</code></div>
        @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="lookup_label" class="form-label">Label</label>
        <input type="text"
               class="form-control @error('label') is-invalid @enderror"
               id="lookup_label"
               name="label"
               value="{{ old('label', $lookupModel?->label) }}"
               required>
        @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label for="lookup_sort_order" class="form-label">Sort Order</label>
        <input type="number"
               class="form-control @error('sort_order') is-invalid @enderror"
               id="lookup_sort_order"
               name="sort_order"
               min="0"
               value="{{ old('sort_order', $lookupModel?->sort_order ?? 0) }}">
        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check mb-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox"
                   class="form-check-input @error('is_active') is-invalid @enderror"
                   id="lookup_is_active"
                   name="is_active"
                   value="1"
                   @checked(old('is_active', $lookupModel?->is_active ?? true))>
            <label class="form-check-label" for="lookup_is_active">Active</label>
            @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    @if ($showFileKind)
        <div class="col-md-4">
            <label for="lookup_file_kind" class="form-label">File Kind</label>
            <select class="form-select @error('meta.file_kind') is-invalid @enderror"
                    id="lookup_file_kind"
                    name="meta[file_kind]"
                    required>
                @foreach (['image' => 'Image', 'pdf' => 'PDF'] as $kind => $kindLabel)
                    <option value="{{ $kind }}"
                        @selected(old('meta.file_kind', $lookupModel?->meta['file_kind'] ?? 'image') === $kind)>
                        {{ $kindLabel }}
                    </option>
                @endforeach
            </select>
            @error('meta.file_kind')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endif

    @if ($showPropertyTypeProfile)
        <div class="col-12">
            <label for="lookup_summary" class="form-label">Summary</label>
            <textarea class="form-control @error('summary') is-invalid @enderror"
                      id="lookup_summary"
                      name="summary"
                      rows="2"
                      maxlength="500">{{ old('summary', $lookupModel?->summary) }}</textarea>
            @error('summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <x-admin.form.rich-text
                label="Description"
                name="description"
                :value="old('description', $lookupModel?->description)"
            />
        </div>

        <div class="col-12">
            @include('media::partials.media-picker', [
                'name' => 'image_id',
                'label' => 'Image',
                'value' => old('image_id', $lookupModel?->image_id),
                'previewUrl' => $lookupModel?->image?->url(),
            ])
        </div>
    @endif
</div>
