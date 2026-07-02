@props([
    'label',
    'name',
    'options' => [],
    'selected' => null,
    'required' => false,
])

<div class="mb-2">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->class(['form-select', 'is-invalid' => $errors->has($name)]) }}
    >
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(old($name, $selected) == $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
