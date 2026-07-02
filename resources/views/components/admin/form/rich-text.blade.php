@props([
    'label',
    'name',
    'value' => null,
    'required' => false,
    'oldKey' => null,
])

@php
    $fieldId = 'field_'.preg_replace('/[^a-zA-Z0-9_-]+/', '_', $oldKey ?? $name);
    $resolvedValue = old($oldKey ?? $name, $value);
@endphp

<div
    class="mb-3 rich-text-field"
    data-rich-text
    {{ $attributes->has('data-rich-text-compact') ? 'data-rich-text-compact' : '' }}
>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <label for="{{ $fieldId }}" class="form-label mb-0">{{ $label }}</label>
        <div class="btn-group btn-group-sm" role="group" aria-label="Editor mode">
            <button type="button" class="btn btn-outline-secondary active" data-rich-text-mode="visual">Visual</button>
            <button type="button" class="btn btn-outline-secondary" data-rich-text-mode="source">Source</button>
        </div>
    </div>

    <div data-rich-text-visual>
        <textarea
            id="{{ $fieldId }}"
            name="{{ $name }}"
            data-rich-text-target
            @if ($required) required @endif
            {{ $attributes->except(['data-rich-text-compact'])->class(['form-control', 'is-invalid' => $errors->has($oldKey ?? $name)]) }}
        >{{ $resolvedValue }}</textarea>
    </div>

    <div data-rich-text-source class="d-none">
        <div data-rich-text-codemirror class="rich-text-codemirror"></div>
    </div>

    @error($oldKey ?? $name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
