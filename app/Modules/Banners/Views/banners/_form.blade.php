@if ($errors->any())
    <div class="alert alert-danger small">
        Please review the highlighted fields and try again.
    </div>
@endif

<div class="row g-3 banner-editor">
    <div class="col-lg-7 banner-editor-main">
        <div class="card">
            <div class="card-header py-2 small">General</div>
            <div class="card-body p-3">
            <div class="row g-3 align-items-stretch">
                <div class="col-xl-8">
                    <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small text-muted">Banner Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $form['name']) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted">Key</label>
                    <input type="text" class="form-control @error('key') is-invalid @enderror" name="key" value="{{ old('key', $form['key']) }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*">
                    <div class="form-text">Used by theme developers, e.g. <code>homepage-hero</code></div>
                    @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted">Template</label>
                    <select class="form-select" name="template_id" required data-banner-template-select>
                        @foreach ($templates as $template)
                            <option
                                value="{{ $template->id }}"
                                data-template-key="{{ $template->key }}"
                                data-template-name="{{ $template->name }}"
                                data-template-category="{{ $template->category }}"
                                data-template-description="{{ $template->description }}"
                                @selected((int) old('template_id', $form['template_id'] ?? $templates->first()?->id) === $template->id)
                            >{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="banner-template-preview h-100" data-banner-template-preview>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="small" data-template-preview-name>Template Preview</div>
                                <div class="text-muted small" data-template-preview-category>Choose a template</div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary" data-template-preview-key>Preview</span>
                        </div>
                        <div class="banner-template-preview-stage" data-template-preview-stage></div>
                        <div class="text-muted small mt-2" data-template-preview-description>Preview updates when the template changes.</div>
                    </div>
                </div>
            </div>
            </div>
        </div>

        <div id="banner-slide-fields" data-banner-slide-fields>
            @foreach ($form['slides'] as $slideIndex => $slide)
                @include('banners::banners._slide_fields', [
                    'slideIndex' => $slideIndex,
                    'slide' => $slide,
                    'form' => $form,
                ])
            @endforeach
        </div>
    </div>

    <div class="col-lg-3 banner-editor-sidebar">
        <div class="accordion banner-editor-accordion" id="bannerEditorAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="bannerScheduleHeading">
                    <button class="accordion-button collapsed py-2 small" type="button" data-bs-toggle="collapse" data-bs-target="#bannerSchedulePanel" aria-expanded="false" aria-controls="bannerSchedulePanel">
                        Publishing
                    </button>
                </h2>
                <div id="bannerSchedulePanel" class="accordion-collapse collapse" aria-labelledby="bannerScheduleHeading" data-bs-parent="#bannerEditorAccordion">
                    <div class="accordion-body p-3">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small text-muted">Status</label>
                                <select class="form-select" name="status">
                                    @foreach ($statuses as $s)
                                        <option value="{{ $s->value }}" @selected(old('status', $form['status']) === $s->value)>{{ $s->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Sort Order</label>
                                <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', $form['sort_order']) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="bannerTemplatePropsHeading">
                    <button class="accordion-button collapsed py-2 small" type="button" data-bs-toggle="collapse" data-bs-target="#bannerTemplatePropsPanel" aria-expanded="false" aria-controls="bannerTemplatePropsPanel">
                        Template Properties
                    </button>
                </h2>
                <div id="bannerTemplatePropsPanel" class="accordion-collapse collapse" aria-labelledby="bannerTemplatePropsHeading" data-bs-parent="#bannerEditorAccordion">
                    <div class="accordion-body p-3">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small text-muted">Column Ratio</label>
                                <select class="form-select" name="column_ratio">
                                    @foreach (['50/50', '40/60', '60/40'] as $ratio)
                                        <option value="{{ $ratio }}" @selected(old('column_ratio', $form['column_ratio']) === $ratio)>{{ $ratio }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Effect</label>
                                <select class="form-select" name="effect">
                                    @foreach (['none' => 'None', 'fade' => 'Fade', 'slide' => 'Slide', 'zoom' => 'Zoom', 'ken_burns' => 'Ken Burns', 'scale' => 'Scale', 'blur_reveal' => 'Blur Reveal'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('effect', $form['effect']) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted">Speed</label>
                                <input type="number" class="form-control" name="animation_speed" value="{{ old('animation_speed', $form['animation_speed']) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted">Delay</label>
                                <input type="number" class="form-control" name="delay" value="{{ old('delay', $form['delay']) }}">
                            </div>
                            <div class="col-6">
                                <div class="form-check small">
                                    <input type="hidden" name="loop" value="0">
                                    <input class="form-check-input" type="checkbox" name="loop" value="1" id="banner-loop" @checked(old('loop', $form['loop']))>
                                    <label class="form-check-label" for="banner-loop">Loop</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check small">
                                    <input type="hidden" name="autoplay" value="0">
                                    <input class="form-check-input" type="checkbox" name="autoplay" value="1" id="banner-autoplay" @checked(old('autoplay', $form['autoplay']))>
                                    <label class="form-check-label" for="banner-autoplay">Autoplay</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($banner) && $banner ? 'Save Banner' : 'Create Banner' }}</button>
    <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.querySelector('[data-banner-template-select]');
            const preview = document.querySelector('[data-banner-template-preview]');
            const templateSchemas = @json($form['template_schemas']);

            if (!select || !preview) {
                return;
            }

            const stage = preview.querySelector('[data-template-preview-stage]');
            const name = preview.querySelector('[data-template-preview-name]');
            const category = preview.querySelector('[data-template-preview-category]');
            const key = preview.querySelector('[data-template-preview-key]');
            const description = preview.querySelector('[data-template-preview-description]');

            const templateMarkup = (templateKey) => {
                if (templateKey === 'image_carousel') {
                    return '<div class="tpl tpl-carousel"><div class="tpl-slide active"></div><div class="tpl-slide"></div><div class="tpl-slide"></div></div>';
                }

                if (templateKey === 'video_hero') {
                    return '<div class="tpl tpl-video"><div class="tpl-play">PLAY</div><div class="tpl-copy"></div></div>';
                }

                if (templateKey === 'split_layout' || templateKey === 'two_column_full_width') {
                    return '<div class="tpl tpl-split"><div class="tpl-media"></div><div class="tpl-copy"></div></div>';
                }

                if (templateKey === 'three_column_full_width') {
                    return '<div class="tpl tpl-three"><div></div><div></div><div></div></div>';
                }

                if (templateKey === 'four_column_full_width' || templateKey === 'five_column_full_width' || templateKey === 'six_column_full_width') {
                    const count = templateKey === 'four_column_full_width' ? 4 : templateKey === 'five_column_full_width' ? 5 : 6;
                    const cell = '<div style="border-radius:0.35rem;background:rgba(255,255,255,0.78)"></div>';
                    return `<div class="tpl" style="display:grid;grid-template-columns:repeat(${count},1fr)">${cell.repeat(count)}</div>`;
                }

                if (templateKey === 'image_left') {
                    return '<div class="tpl tpl-split"><div class="tpl-media"></div><div class="tpl-copy tpl-copy-lines"></div></div>';
                }

                if (templateKey === 'image_right') {
                    return '<div class="tpl tpl-split"><div class="tpl-copy tpl-copy-lines"></div><div class="tpl-media"></div></div>';
                }

                if (templateKey === 'minimal') {
                    return '<div class="tpl tpl-minimal"><div class="tpl-copy"></div></div>';
                }

                if (templateKey === 'inner_page') {
                    return '<div class="tpl tpl-inner-page"><div class="tpl-copy tpl-copy-compact"></div></div>';
                }

                const align = templateKey === 'hero_left' ? 'left' : templateKey === 'hero_right' ? 'right' : 'center';
                return `<div class="tpl tpl-hero tpl-${align}"><div class="tpl-copy"></div></div>`;
            };

            const render = () => {
                const option = select.selectedOptions[0];
                const templateKey = option?.dataset.templateKey || 'hero_center';
                const schema = templateSchemas[select.value] || {};

                name.textContent = option?.dataset.templateName || option?.textContent || 'Template Preview';
                category.textContent = option?.dataset.templateCategory || 'template';
                key.textContent = templateKey.replaceAll('_', ' ');
                description.textContent = option?.dataset.templateDescription || 'Reusable banner template.';
                stage.innerHTML = templateMarkup(templateKey);

                const blockCount = (schema.blocks || []).length;
                const blockMediaCount = (schema.blocks || []).filter((block) => block.mediaSlot).length;
                const slideHint = schema.slides === 'many'
                    ? `Up to ${schema.maxSlides || 5} slides`
                    : blockMediaCount > 0
                        ? `${blockCount} column(s), ${blockMediaCount} picture(s) each`
                        : `${blockCount} block(s), ${(schema.mediaSlots || []).length} media slot(s)`;
                description.textContent = `${description.textContent} ${slideHint}.`;
            };

            select.addEventListener('change', () => {
                const url = new URL(window.location.href);
                url.searchParams.set('template_id', select.value);
                window.location.href = url.toString();
            });
            render();
        });
    </script>
@endpush
