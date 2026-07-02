@php
    $schemaService = app(\App\Modules\Banners\Services\BannerFormSchemaService::class);
    $buttonStyles = ['primary' => 'Primary', 'secondary' => 'Secondary', 'outline-primary' => 'Outline'];
    $buttonCount = max(0, (int) ($form['active_schema']['buttons']['count'] ?? 1));
    $hasSlideMedia = ! empty($form['active_schema']['mediaSlots']) && ! $schemaService->hasPerBlockMedia($form['active_schema']);
    $isCarousel = ($form['active_schema']['slides'] ?? 1) === 'many';
    $templateKey = $form['template_key'] ?? null;
@endphp

@if ($isCarousel && $slideIndex === 0)
    <div class="alert alert-info small py-2 mt-2 mb-0">
        Add up to {{ $form['active_schema']['maxSlides'] ?? 5 }} slides. Each slide supports one image, title, subtitle, text, and CTA. Empty slides are ignored when saving.
    </div>
@endif

<div class="banner-slide-panel card mt-2" data-slide-index="{{ $slideIndex }}">
    @if (($form['active_schema']['slides'] ?? 1) === 'many')
        <div class="card-header py-2 small d-flex justify-content-between align-items-center">
            <span>Slide {{ $slideIndex + 1 }}</span>
            <input type="text" class="form-control w-50" name="slides[{{ $slideIndex }}][name]" value="{{ old('slides.'.$slideIndex.'.name', $slide['name'] ?? 'Slide '.($slideIndex + 1)) }}" placeholder="Slide name">
        </div>
    @endif
    <div class="card-body p-3">
        @foreach (($form['active_schema']['blocks'] ?? []) as $blockIndex => $blockSchema)
            @php
                $block = $slide['blocks'][$blockIndex] ?? [];
                $blockLabel = $blockSchema['label'] ?? 'Content Block';
                $mediaSlot = $blockSchema['mediaSlot'] ?? null;
                $isColumnBlock = $schemaService->isColumnBlock($blockSchema);
                $columnNumber = $isColumnBlock ? (int) str_replace('column_', '', $blockSchema['region'] ?? '') : null;
                $blockColorClass = $schemaService->blockColorClass($blockSchema, $blockIndex);
            @endphp
            <div class="card mb-2 banner-block {{ $blockColorClass }}">
                <div class="card-header py-2 small d-flex justify-content-between align-items-center">
                    <span>{{ $blockLabel }}</span>
                    <span class="d-flex align-items-center gap-2">
                        @if ($isColumnBlock)
                            <span class="banner-block-badge">Col {{ $columnNumber }}</span>
                        @else
                            <span class="banner-block-badge">Content</span>
                        @endif
                    </span>
                </div>
                <div class="card-body p-3">
                    <input type="hidden" name="slides[{{ $slideIndex }}][blocks][{{ $blockIndex }}][region]" value="{{ $blockSchema['region'] ?? 'main' }}">
                    <input type="hidden" name="slides[{{ $slideIndex }}][blocks][{{ $blockIndex }}][type]" value="content">

                    @if ($mediaSlot)
                        @php
                            $mediaName = "slides[{$slideIndex}][media][{$mediaSlot}][media_id]";
                            $mediaOldKey = "slides.{$slideIndex}.media.{$mediaSlot}.media_id";
                            $mediaValue = old($mediaOldKey, $slide['media'][$mediaSlot]['media_id'] ?? null);
                            $mediaUrl = $slide['media'][$mediaSlot]['url'] ?? null;
                        @endphp
                        <div class="banner-block-part banner-block-part--media mb-3">
                            <div class="banner-block-part-header">
                                <span class="banner-block-badge">Media</span>
                            </div>
                            @include('media::partials.media-picker', [
                                'name' => $mediaName,
                                'oldKey' => $mediaOldKey,
                                'label' => $schemaService->mediaSlotLabel($mediaSlot, $blockSchema),
                                'value' => $mediaValue,
                                'previewUrl' => $mediaUrl,
                            ])
                        </div>
                    @endif

                    <div class="row g-2">
                        @foreach ($blockSchema['fields'] ?? [] as $field)
                            @if ($field === 'rich_text')
                                <div class="col-12">
                                    <x-admin.form.rich-text
                                        :label="$schemaService->fieldLabel($field, $blockSchema, $templateKey)"
                                        :name="'slides['.$slideIndex.'][blocks]['.$blockIndex.'][rich_text]'"
                                        :old-key="'slides.'.$slideIndex.'.blocks.'.$blockIndex.'.rich_text'"
                                        :value="old('slides.'.$slideIndex.'.blocks.'.$blockIndex.'.rich_text', $block['rich_text'] ?? '')"
                                        data-rich-text-compact
                                        class="form-control"
                                    />
                                </div>
                            @elseif ($field === 'description')
                                <div class="col-12">
                                    <label class="form-label small text-muted">{{ $schemaService->fieldLabel($field, $blockSchema, $templateKey) }}</label>
                                    <textarea class="form-control" name="slides[{{ $slideIndex }}][blocks][{{ $blockIndex }}][description]" rows="3">{{ old('slides.'.$slideIndex.'.blocks.'.$blockIndex.'.description', $block['description'] ?? '') }}</textarea>
                                </div>
                            @else
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">{{ $schemaService->fieldLabel($field, $blockSchema, $templateKey) }}</label>
                                    <input type="text" class="form-control" name="slides[{{ $slideIndex }}][blocks][{{ $blockIndex }}][{{ $field }}]" value="{{ old('slides.'.$slideIndex.'.blocks.'.$blockIndex.'.'.$field, $block[$field] ?? '') }}">
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if ($buttonCount > 0)
                        <div class="banner-block-part banner-block-part--cta mt-3">
                            <div class="banner-block-part-header mb-2">
                                <span class="banner-block-badge">CTA</span>
                            </div>
                            <div class="row g-2">
                                @for ($buttonIndex = 0; $buttonIndex < $buttonCount; $buttonIndex++)
                                    @php $button = $block['buttons'][$buttonIndex] ?? []; @endphp
                                    <div class="col-12">
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label class="form-label small text-muted">Label</label>
                                                <input type="text" class="form-control" name="slides[{{ $slideIndex }}][blocks][{{ $blockIndex }}][buttons][{{ $buttonIndex }}][label]" value="{{ old('slides.'.$slideIndex.'.blocks.'.$blockIndex.'.buttons.'.$buttonIndex.'.label', $button['label'] ?? '') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small text-muted">URL</label>
                                                <input type="text" class="form-control" name="slides[{{ $slideIndex }}][blocks][{{ $blockIndex }}][buttons][{{ $buttonIndex }}][url]" value="{{ old('slides.'.$slideIndex.'.blocks.'.$blockIndex.'.buttons.'.$buttonIndex.'.url', $button['url'] ?? '') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small text-muted">Target</label>
                                                <select class="form-select" name="slides[{{ $slideIndex }}][blocks][{{ $blockIndex }}][buttons][{{ $buttonIndex }}][target]">
                                                    <option value="_self" @selected(old('slides.'.$slideIndex.'.blocks.'.$blockIndex.'.buttons.'.$buttonIndex.'.target', $button['target'] ?? '_self') === '_self')>Same tab</option>
                                                    <option value="_blank" @selected(old('slides.'.$slideIndex.'.blocks.'.$blockIndex.'.buttons.'.$buttonIndex.'.target', $button['target'] ?? '_self') === '_blank')>New tab</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small text-muted">Style</label>
                                                <select class="form-select" name="slides[{{ $slideIndex }}][blocks][{{ $blockIndex }}][buttons][{{ $buttonIndex }}][style]">
                                                    @foreach ($buttonStyles as $value => $label)
                                                        <option value="{{ $value }}" @selected(old('slides.'.$slideIndex.'.blocks.'.$blockIndex.'.buttons.'.$buttonIndex.'.style', $button['style'] ?? 'primary') === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if ($hasSlideMedia)
            <div class="card banner-block banner-block--media">
                <div class="card-header py-2 small d-flex justify-content-between align-items-center">
                    <span>Media Library Assets</span>
                    <span class="banner-block-badge">Media</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        @foreach ($form['active_schema']['mediaSlots'] as $slot)
                            @php
                                $mediaName = "slides[{$slideIndex}][media][{$slot}][media_id]";
                                $mediaOldKey = "slides.{$slideIndex}.media.{$slot}.media_id";
                                $mediaValue = old($mediaOldKey, $slide['media'][$slot]['media_id'] ?? null);
                                $mediaUrl = $slide['media'][$slot]['url'] ?? null;
                            @endphp
                            <div class="col-md-4">
                                @include('media::partials.media-picker', [
                                    'name' => $mediaName,
                                    'oldKey' => $mediaOldKey,
                                    'label' => $schemaService->mediaSlotLabel($slot),
                                    'value' => $mediaValue,
                                    'previewUrl' => $mediaUrl,
                                ])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
