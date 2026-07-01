<h6 class="fw-semibold mb-3">SEO Settings</h6>

<div class="mb-3">
    <label for="seo_title" class="form-label">SEO Title</label>
    <input type="text" class="form-control @error('seo_title') is-invalid @enderror" id="seo_title" name="seo_title"
           value="{{ old('seo_title', $seo?->seo_title) }}">
    @error('seo_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="meta_description" class="form-label">Meta Description</label>
    <textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="2">{{ old('meta_description', $seo?->meta_description) }}</textarea>
    @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="meta_keywords" class="form-label">Meta Keywords</label>
    <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" name="meta_keywords"
           value="{{ old('meta_keywords', $seo?->meta_keywords) }}">
    @error('meta_keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="canonical_url" class="form-label">Canonical URL</label>
    <input type="url" class="form-control @error('canonical_url') is-invalid @enderror" id="canonical_url" name="canonical_url"
           value="{{ old('canonical_url', $seo?->canonical_url) }}">
    @error('canonical_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="og_title" class="form-label">Open Graph Title</label>
        <input type="text" class="form-control" id="og_title" name="og_title" value="{{ old('og_title', $seo?->og_title) }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="robots" class="form-label">Robots</label>
        <select class="form-select" id="robots" name="robots">
            @foreach (['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'] as $robots)
                <option value="{{ $robots }}" {{ old('robots', $seo?->robots ?? 'index,follow') === $robots ? 'selected' : '' }}>{{ $robots }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-3">
    <label for="og_description" class="form-label">Open Graph Description</label>
    <textarea class="form-control" id="og_description" name="og_description" rows="2">{{ old('og_description', $seo?->og_description) }}</textarea>
</div>
