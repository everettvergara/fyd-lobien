@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'required' => false,
])

<div class="mb-2">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}
    >
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
