@props([
    'status' => null,
    'label' => null,
    'variant' => 'primary',
])

@php
    $text = $label ?? (is_object($status) && method_exists($status, 'label') ? $status->label() : (string) $status);
    $classes = match ($variant) {
        'secondary' => 'bg-secondary-subtle text-secondary',
        default => 'bg-primary-subtle text-primary',
    };
@endphp

<span {{ $attributes->class(['badge', $classes]) }}>{{ $text }}</span>
