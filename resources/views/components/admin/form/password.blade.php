@props([
    'label',
    'name',
    'id' => null,
    'required' => false,
    'autocomplete' => null,
])

<div {{ $attributes->class(['mb-3']) }}>
    <label for="{{ $id ?? $name }}" class="form-label">{{ $label }}</label>
    <div class="input-group">
        <input
            type="password"
            id="{{ $id ?? $name }}"
            name="{{ $name }}"
            @if ($required) required @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            {{ $attributes->except('class')->class(['form-control', 'is-invalid' => $errors->has($name)]) }}
        >
        <button type="button"
                class="btn btn-outline-secondary"
                data-password-toggle
                aria-label="Show password"
                aria-pressed="false">
            <i class="bi bi-eye" aria-hidden="true"></i>
        </button>
    </div>
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
