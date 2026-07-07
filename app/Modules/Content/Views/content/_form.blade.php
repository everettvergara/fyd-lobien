@php
    $isEdit = isset($content);
    $defaultType = $defaultContentType ?? 'page';
    $contentTypeDefinitions = $contentTypeDefinitions ?? app(\App\Support\ContentTypeRegistry::class)->all();
    $hasPublishingErrors = $errors->hasAny(['content_type', 'status']);
    $hasFeaturedImageErrors = $errors->hasAny(['featured_image_id', 'gallery_media_ids', 'gallery_media_ids.*']);
    $hasAttachmentErrors = $errors->hasAny(['attachment_id', 'url_link']);
@endphp

<div class="alert alert-info small">
    Public pages and SEO are managed in <a href="{{ route('admin.pages.index') }}">Page Manager</a>. Content entries here are legacy article/blog storage.
</div>

<div class="content-form">
    <section class="content-form-main mb-4">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $content?->title) }}" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label">URL Slug (URI path)</label>
            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $content?->slug) }}" required placeholder="about-us">
            <div class="form-text">Public URL will be <code>/your-slug</code>. Use lowercase letters, numbers, and hyphens only.</div>
            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="summary" class="form-label">Summary</label>
            <textarea class="form-control" id="summary" name="summary" rows="2">{{ old('summary', $content?->summary) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="url_link" class="form-label">URL Link</label>
            <input type="url" class="form-control @error('url_link') is-invalid @enderror" id="url_link" name="url_link" value="{{ old('url_link', $content?->url_link) }}" placeholder="https://example.com/reference">
            <div class="form-text">External reference URL rendered by the theme.</div>
            @error('url_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <x-admin.form.rich-text
                label="Body"
                name="body"
                :value="old('body', $content?->body)"
            />
        </div>
    </section>

    <aside class="content-form-sidebar">
        <div class="row">
            <div class="col-lg-4">
                <div class="accordion content-form-sidebar" id="contentFormSidebar">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="contentPublishingHeading">
                            <button
                                class="accordion-button py-2 {{ $hasPublishingErrors ? '' : 'collapsed' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#contentPublishingPanel"
                                aria-expanded="{{ $hasPublishingErrors ? 'true' : 'false' }}"
                                aria-controls="contentPublishingPanel"
                            >
                                Publishing
                            </button>
                        </h2>
                        <div
                            id="contentPublishingPanel"
                            class="accordion-collapse collapse {{ $hasPublishingErrors ? 'show' : '' }}"
                            aria-labelledby="contentPublishingHeading"
                        >
                            <div class="accordion-body">
                                <div class="mb-3">
                                    <label for="content_type" class="form-label">Content Type</label>
                                    <select class="form-select @error('content_type') is-invalid @enderror" id="content_type" name="content_type" required>
                                        @foreach ($contentTypeOptions as $value => $label)
                                            <option
                                                value="{{ $value }}"
                                                title="{{ $contentTypeDefinitions[$value]['description'] ?? '' }}"
                                                {{ old('content_type', $content?->content_type ?? $defaultType) === $value ? 'selected' : '' }}
                                            >{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @if ($selectedType = old('content_type', $content?->content_type ?? $defaultType))
                                        @if ($description = ($contentTypeDefinitions[$selectedType]['description'] ?? null))
                                            <div class="form-text">{{ $description }}</div>
                                        @endif
                                    @endif
                                    @error('content_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-0">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->value }}" {{ old('status', $content?->status?->value) === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="contentFeaturedImageHeading">
                            <button
                                class="accordion-button py-2 {{ $hasFeaturedImageErrors ? '' : 'collapsed' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#contentFeaturedImagePanel"
                                aria-expanded="{{ $hasFeaturedImageErrors ? 'true' : 'false' }}"
                                aria-controls="contentFeaturedImagePanel"
                            >
                                Images
                            </button>
                        </h2>
                        <div
                            id="contentFeaturedImagePanel"
                            class="accordion-collapse collapse {{ $hasFeaturedImageErrors ? 'show' : '' }}"
                            aria-labelledby="contentFeaturedImageHeading"
                        >
                            <div class="accordion-body">
                                @include('media::partials.media-picker', [
                                    'name' => 'gallery_media_ids',
                                    'label' => 'Gallery',
                                    'mode' => 'multi',
                                    'values' => $content?->galleryImages?->pluck('id')->all() ?? [],
                                ])
                                <div class="form-text">The first image is used as the featured image.</div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="contentAttachmentsHeading">
                            <button
                                class="accordion-button py-2 {{ $hasAttachmentErrors ? '' : 'collapsed' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#contentAttachmentsPanel"
                                aria-expanded="{{ $hasAttachmentErrors ? 'true' : 'false' }}"
                                aria-controls="contentAttachmentsPanel"
                            >
                                Attachments
                            </button>
                        </h2>
                        <div
                            id="contentAttachmentsPanel"
                            class="accordion-collapse collapse {{ $hasAttachmentErrors ? 'show' : '' }}"
                            aria-labelledby="contentAttachmentsHeading"
                        >
                            <div class="accordion-body">
                                @include('media::partials.media-picker', [
                                    'name' => 'attachment_id',
                                    'label' => 'PDF Attachment',
                                    'mode' => 'single',
                                    'value' => $content?->attachment_id,
                                    'type' => 'pdf',
                                ])
                                @error('attachment_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</div>
