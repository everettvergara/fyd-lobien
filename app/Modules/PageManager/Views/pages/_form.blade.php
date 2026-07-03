@php
    $isEdit = isset($page);
    $seo = $page?->seoMeta;
    $hasPublishingErrors = $errors->hasAny(['status', 'path']);
    $hasSeoErrors = collect(\App\Support\SeoFields::attributeKeys())->contains(fn (string $field) => $errors->has($field));
    $initialBlocks = old('blocks', $isEdit
        ? $page->blocks->map(fn ($block) => [
            'region_key' => $block->region_key,
            'block_type' => $block->block_type,
            'sort_order' => $block->sort_order,
            'config' => $block->config ?? [],
        ])->values()->all()
        : []);
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $page?->title) }}" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="path" class="form-label">Public Path</label>
            <input
                type="text"
                class="form-control @error('path') is-invalid @enderror"
                id="path"
                name="path"
                value="{{ old('path', $page?->path ?? '/') }}"
                {{ ($page?->is_system ?? false) ? 'readonly' : '' }}
                required
                placeholder="/about"
            >
            <div class="form-text">Use <code>/</code> for homepage. Example: <code>/about</code></div>
            @error('path')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="summary" class="form-label">Summary</label>
            <textarea class="form-control" id="summary" name="summary" rows="2">{{ old('summary', $page?->summary) }}</textarea>
        </div>

        <div class="mb-4">
            <x-admin.form.rich-text label="Body" name="body" :value="old('body', $page?->body)" />
        </div>

        @include('pagemanager::partials.block-editor', [
            'regions' => $regions,
            'blockPalette' => $blockPalette,
            'initialBlocks' => $initialBlocks,
            'inputPrefix' => 'blocks',
        ])
    </div>

    <div class="col-lg-4">
        <div class="accordion" id="pageFormSidebar">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button py-2 {{ $hasPublishingErrors ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#pagePublishingPanel">
                        Publishing
                    </button>
                </h2>
                <div id="pagePublishingPanel" class="accordion-collapse collapse {{ $hasPublishingErrors ? 'show' : '' }}">
                    <div class="accordion-body">
                        <div class="mb-0">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" {{ old('status', $page?->status?->value) === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button py-2 {{ $hasSeoErrors ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#pageSeoPanel">
                        SEO Settings
                    </button>
                </h2>
                <div id="pageSeoPanel" class="accordion-collapse collapse {{ $hasSeoErrors ? 'show' : '' }}">
                    <div class="accordion-body">
                        @include('seo::partials.seo-fields', ['seo' => $seo, 'sidebar' => true])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
