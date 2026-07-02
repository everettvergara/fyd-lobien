@php
    $seoFields = \App\Support\SeoFields::attributeKeys();
    $hasSeoErrors = collect($seoFields)->contains(fn (string $field) => $errors->has($field));
    $sidebar = $sidebar ?? false;
@endphp

@if ($sidebar)
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

    <div class="mb-3">
        <label for="og_title" class="form-label">Open Graph Title</label>
        <input type="text" class="form-control" id="og_title" name="og_title" value="{{ old('og_title', $seo?->og_title) }}">
    </div>

    <div class="mb-3">
        <label for="robots" class="form-label">Robots</label>
        <select class="form-select" id="robots" name="robots">
            @foreach (['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'] as $robots)
                <option value="{{ $robots }}" {{ old('robots', $seo?->robots ?? 'index,follow') === $robots ? 'selected' : '' }}>{{ $robots }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label for="og_description" class="form-label">Open Graph Description</label>
        <textarea class="form-control" id="og_description" name="og_description" rows="2">{{ old('og_description', $seo?->og_description) }}</textarea>
    </div>

    @include('media::partials.media-picker', [
        'name' => 'og_image_id',
        'label' => 'Open Graph Image',
        'value' => $seo?->og_image_id,
        'previewUrl' => $seo?->ogImage?->url() ?? null,
    ])

    <hr class="my-3">
    <h6 class="text-muted text-uppercase small mb-3">Sitemap</h6>

    <div class="mb-3 form-check">
        <input type="hidden" name="sitemap_include" value="0">
        <input type="checkbox" class="form-check-input" id="sitemap_include" name="sitemap_include" value="1"
               {{ old('sitemap_include', $seo?->sitemap_include ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="sitemap_include">Include in sitemap</label>
    </div>

    <div class="mb-3">
        <label for="sitemap_changefreq" class="form-label">Change Frequency</label>
        <select class="form-select @error('sitemap_changefreq') is-invalid @enderror" id="sitemap_changefreq" name="sitemap_changefreq">
            <option value="">Use default</option>
            @foreach (\App\Enums\SitemapChangeFrequency::options() as $value => $label)
                <option value="{{ $value }}" {{ old('sitemap_changefreq', $seo?->sitemap_changefreq) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('sitemap_changefreq')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="sitemap_priority" class="form-label">Priority</label>
        <input type="number" class="form-control @error('sitemap_priority') is-invalid @enderror" id="sitemap_priority" name="sitemap_priority"
               value="{{ old('sitemap_priority', $seo?->sitemap_priority) }}" min="0" max="1" step="0.1" placeholder="Use default">
        @error('sitemap_priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
@else
<div class="accordion" id="seoSettingsAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button
                class="accordion-button {{ $hasSeoErrors ? '' : 'collapsed' }}"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#seoSettingsPanel"
                aria-expanded="{{ $hasSeoErrors ? 'true' : 'false' }}"
                aria-controls="seoSettingsPanel"
            >
                SEO Settings
            </button>
        </h2>
        <div
            id="seoSettingsPanel"
            class="accordion-collapse collapse {{ $hasSeoErrors ? 'show' : '' }}"
            data-bs-parent="#seoSettingsAccordion"
        >
            <div class="accordion-body">
                @include('seo::partials.seo-fields', ['seo' => $seo, 'sidebar' => true])
            </div>
        </div>
    </div>
</div>
@endif
